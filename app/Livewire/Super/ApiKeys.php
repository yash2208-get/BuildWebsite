<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ApiKeys extends ResourceComponent
{
    public bool $showCreate = false;

    public string $keyName = '';
    public ?int $ownerId = null;
    public int $rateLimit = 60;
    public array $abilities = ['websites:read'];
    public ?string $expiresAt = null;
    public ?string $plainKey = null;

    protected function title(): string
    {
        return 'API Key Management';
    }

    protected function view(): string
    {
        return 'livewire.super.api-keys';
    }

    protected function searchable(): array
    {
        return ['name', 'user.name', 'user.email'];
    }

    protected function query(): Builder
    {
        return ApiKey::query()->with('user');
    }

    public function createKey(): void
    {
        $data = $this->validate([
            'keyName' => ['required', 'string', 'min:2', 'max:80'],
            'ownerId' => ['nullable', 'exists:users,id'],
            'rateLimit' => ['required', 'integer', 'min:10', 'max:100000'],
            'expiresAt' => ['nullable', 'date', 'after:today'],
        ]);

        [$key, $plain] = ApiKey::generate([
            'user_id' => $data['ownerId'],
            'name' => $data['keyName'],
            'abilities' => $this->abilities,
            'rate_limit' => $data['rateLimit'],
            'expires_at' => $data['expiresAt'],
        ]);

        $this->plainKey = $plain;
        $this->showCreate = false;
        $this->reset('keyName', 'ownerId');

        ActivityLog::record('created', "API key \"{$key->name}\" issued", $key);
        $this->notifySuccess('API key created — copy it now, it cannot be shown again.');
    }

    public function toggleAbility(string $ability): void
    {
        in_array($ability, $this->abilities, true)
            ? $this->abilities = array_values(array_diff($this->abilities, [$ability]))
            : $this->abilities[] = $ability;
    }

    public function revoke(int $id): void
    {
        $key = ApiKey::findOrFail($id);
        $key->delete();

        ActivityLog::record('deleted', "API key \"{$key->name}\" revoked");
        $this->notifySuccess('API key revoked.');
    }

    protected function viewData(): array
    {
        return [
            'users' => User::orderBy('name')->limit(200)->get(['id', 'name', 'email']),
            'allAbilities' => [
                'websites:read' => 'Read websites',
                'websites:write' => 'Create & update websites',
                'websites:publish' => 'Publish websites',
                'pages:read' => 'Read pages',
                'pages:write' => 'Create & update pages',
                'media:read' => 'Read media',
                'media:write' => 'Upload media',
                'analytics:read' => 'Read analytics',
            ],
            'totals' => [
                'all' => ApiKey::count(),
                'active' => ApiKey::where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
                'requests' => (int) ApiKey::sum('usage_count'),
            ],
        ];
    }
}
