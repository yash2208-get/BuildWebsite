<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\Backup;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Throwable;

#[Layout('layouts.app')]
class SystemHealth extends BaseComponent
{
    public function refreshChecks(): void
    {
        $this->notifySuccess('Health checks re-run.');
    }

    /** @return array{status: string, message: string, latency: float|null} */
    private function check(callable $probe): array
    {
        $start = microtime(true);

        try {
            $message = $probe();

            return [
                'status' => 'ok',
                'message' => $message ?: 'Operational',
                'latency' => round((microtime(true) - $start) * 1000, 1),
            ];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => $e->getMessage(), 'latency' => null];
        }
    }

    public function render()
    {
        $checks = [
            'Database' => $this->check(function () {
                DB::connection()->getPdo();
                $driver = DB::connection()->getDriverName();
                $tables = count(DB::select("SELECT name FROM sqlite_master WHERE type='table'"));

                return ucfirst($driver).' connected · '.$tables.' tables';
            }),
            'Cache' => $this->check(function () {
                Cache::put('health:probe', 'ok', 10);

                return Cache::get('health:probe') === 'ok'
                    ? ucfirst(config('cache.default')).' store responding'
                    : throw new \RuntimeException('Cache read-back failed');
            }),
            'Storage' => $this->check(function () {
                Storage::disk('local')->put('health.txt', 'ok');
                $ok = Storage::disk('local')->get('health.txt') === 'ok';
                Storage::disk('local')->delete('health.txt');

                return $ok ? 'Read/write verified' : throw new \RuntimeException('Storage write failed');
            }),
            'Queue' => $this->check(function () {
                $pending = DB::table('jobs')->count();
                $failed = DB::table('failed_jobs')->count();

                return config('queue.default').' · '.$pending.' pending, '.$failed.' failed';
            }),
            'Scheduler' => $this->check(fn () => 'Last heartbeat '.(Cache::get('scheduler:last_run') ?? 'not recorded')),
            'Mail' => $this->check(fn () => ucfirst(config('mail.default')).' transport configured'),
        ];

        return view('livewire.super.system-health', [
            'checks' => $checks,
            'healthy' => collect($checks)->every(fn ($c) => $c['status'] === 'ok'),
            'environment' => [
                'PHP version' => PHP_VERSION,
                'Laravel version' => app()->version(),
                'Environment' => app()->environment(),
                'Debug mode' => config('app.debug') ? 'Enabled' : 'Disabled',
                'Timezone' => config('app.timezone'),
                'Locale' => config('app.locale'),
                'Cache driver' => config('cache.default'),
                'Queue driver' => config('queue.default'),
                'Session driver' => config('session.driver'),
                'Mail driver' => config('mail.default'),
                'Database' => DB::connection()->getDriverName(),
                'Memory limit' => ini_get('memory_limit'),
                'Max upload' => ini_get('upload_max_filesize'),
                'Max execution' => ini_get('max_execution_time').'s',
            ],
            'metrics' => [
                'users' => User::count(),
                'websites' => Website::count(),
                'db_size' => $this->databaseSizeMb(),
                'memory' => round(memory_get_peak_usage(true) / 1048576, 1),
                'backups' => Backup::count(),
                'last_backup' => Backup::latest()->first()?->created_at,
            ],
        ])->layoutData($this->layoutData('System Health'));
    }

    private function databaseSizeMb(): float
    {
        try {
            $path = config('database.connections.'.config('database.default').'.database');

            return is_string($path) && is_file($path) ? round(filesize($path) / 1048576, 2) : 0.0;
        } catch (Throwable) {
            return 0.0;
        }
    }
}
