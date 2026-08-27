<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\ResourceComponent;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ActivityLogs extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';


    protected function title(): string
    {
        return 'Activity Logs';
    }

    protected function view(): string
    {
        return 'livewire.admin.activity-logs';
    }

    protected function searchable(): array
    {
        return ['description', 'action'];
    }

    protected function query(): Builder
    {
        return ActivityLog::query()
            ->with('user');
    }

    protected function viewData(): array
    {
        return [
            'events' => ActivityLog::query()->select('event')->distinct()->orderBy('event')->pluck('event'),
        ];
    }
}
