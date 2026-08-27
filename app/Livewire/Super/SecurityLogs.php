<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\SecurityLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SecurityLogs extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';


    protected function title(): string
    {
        return 'Security Logs';
    }

    protected function view(): string
    {
        return 'livewire.super.security-logs';
    }

    protected function searchable(): array
    {
        return ['event', 'ip_address'];
    }

    protected function query(): Builder
    {
        return SecurityLog::query()
            ->with('user');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => SecurityLog::count(),
                'warnings' => SecurityLog::where('level', 'warning')->count(),
                'today' => SecurityLog::whereDate('created_at', today())->count(),
            ],
            'events' => SecurityLog::query()->select('event')->distinct()->orderBy('event')->pluck('event'),
        ];
    }
}
