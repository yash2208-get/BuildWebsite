<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Throwable;

#[Layout('layouts.app')]
class DatabaseManager extends BaseComponent
{
    public string $tab = 'tables';

    public string $query = '';

    public ?array $queryResult = null;

    public ?string $queryError = null;

    public function runCache(string $action): void
    {
        $this->authorize('manage-settings');

        $commands = [
            'clear' => ['cache:clear', 'Application cache cleared.'],
            'config' => ['config:clear', 'Configuration cache cleared.'],
            'route' => ['route:clear', 'Route cache cleared.'],
            'view' => ['view:clear', 'Compiled views cleared.'],
            'optimize' => ['optimize:clear', 'All caches cleared.'],
        ];

        if (! isset($commands[$action])) {
            return;
        }

        [$command, $message] = $commands[$action];

        Artisan::call($command);
        ActivityLog::record('updated', "Ran {$command} from the database manager");

        $this->notifySuccess($message);
    }

    public function optimizeTables(): void
    {
        $this->authorize('manage-settings');

        try {
            DB::connection()->getDriverName() === 'sqlite'
                ? DB::statement('VACUUM')
                : DB::statement('OPTIMIZE TABLE '.implode(',', $this->tableNames()));

            $this->notifySuccess('Database optimised successfully.');
        } catch (Throwable $e) {
            $this->notifyError('Optimisation failed: '.$e->getMessage());
        }
    }

    /**
     * Executes a read-only query. Anything that could mutate data is rejected
     * outright rather than relying on transaction rollback.
     */
    public function runQuery(): void
    {
        $this->authorize('manage-settings');

        $this->queryResult = null;
        $this->queryError = null;

        $sql = trim($this->query);

        if (! preg_match('/^\s*select\s/i', $sql)) {
            $this->queryError = 'Only SELECT statements are permitted here.';

            return;
        }

        if (preg_match('/\b(insert|update|delete|drop|alter|truncate|create|attach|pragma)\b/i', $sql)) {
            $this->queryError = 'That statement contains a disallowed keyword.';

            return;
        }

        try {
            $rows = DB::select($sql.(str_contains(strtolower($sql), 'limit') ? '' : ' LIMIT 200'));

            $this->queryResult = [
                'columns' => $rows ? array_keys((array) $rows[0]) : [],
                'rows' => array_map(fn ($r) => (array) $r, $rows),
                'count' => count($rows),
            ];
        } catch (Throwable $e) {
            $this->queryError = $e->getMessage();
        }
    }

    private function tableNames(): array
    {
        return collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
            ->pluck('name')->all();
    }

    public function render()
    {
        $tables = collect($this->tableNames())->map(function (string $name) {
            try {
                $count = DB::table($name)->count();
            } catch (Throwable) {
                $count = 0;
            }

            return ['name' => $name, 'rows' => $count, 'columns' => count(DB::getSchemaBuilder()->getColumnListing($name))];
        })->sortByDesc('rows')->values();

        $path = config('database.connections.'.config('database.default').'.database');

        return view('livewire.super.database-manager', [
            'tables' => $tables,
            'totalRows' => $tables->sum('rows'),
            'dbSize' => is_string($path) && is_file($path) ? round(filesize($path) / 1048576, 2) : 0,
            'driver' => DB::connection()->getDriverName(),
            'migrations' => DB::table('migrations')->orderByDesc('id')->limit(12)->get(),
        ])->layoutData($this->layoutData('Database Manager'));
    }
}
