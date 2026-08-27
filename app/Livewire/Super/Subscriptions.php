<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Subscription;
use App\Services\Billing\SubscriptionService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Subscriptions extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function cancelSubscription(int $id): void
    {
        app(SubscriptionService::class)->cancel(Subscription::findOrFail($id));
        $this->notifySuccess('Subscription cancelled.');
    }

    protected function title(): string
    {
        return 'Subscriptions';
    }

    protected function view(): string
    {
        return 'livewire.super.subscriptions';
    }

    protected function searchable(): array
    {
        return ['user.name', 'user.email'];
    }

    protected function query(): Builder
    {
        return Subscription::query()
            ->with('user', 'plan');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'active' => Subscription::where('status', 'active')->count(),
                'cancelled' => Subscription::where('status', 'canceled')->count(),
                'mrr' => round((float) Subscription::where('status', 'active')->where('billing_cycle', 'monthly')->sum('amount'), 2),
            ],
        ];
    }
}
