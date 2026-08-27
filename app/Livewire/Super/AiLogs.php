<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\AiGeneration;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AiLogs extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';


    protected function title(): string
    {
        return 'AI Generation Logs';
    }

    protected function view(): string
    {
        return 'livewire.super.ai-logs';
    }

    protected function searchable(): array
    {
        return ['prompt', 'user.name'];
    }

    protected function query(): Builder
    {
        return AiGeneration::query()
            ->with('user');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => AiGeneration::count(),
                'completed' => AiGeneration::where('status', 'completed')->count(),
                'failed' => AiGeneration::where('status', 'failed')->count(),
                'credits' => (int) AiGeneration::sum('credits_used'),
                'tokens' => (int) AiGeneration::sum('tokens_used'),
            ],
        ];
    }
}
