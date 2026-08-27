<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Invoices extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function markPaid(int $id): void
    {
        Invoice::findOrFail($id)->update(['status' => 'paid', 'paid_at' => now()]);
        $this->notifySuccess('Invoice marked as paid.');
    }

    public function refund(int $id): void
    {
        Invoice::findOrFail($id)->update(['status' => 'refunded']);
        $this->notifySuccess('Invoice refunded.');
    }

    protected function title(): string
    {
        return 'Invoices';
    }

    protected function view(): string
    {
        return 'livewire.super.invoices';
    }

    protected function searchable(): array
    {
        return ['invoice_number', 'user.name', 'user.email'];
    }

    protected function query(): Builder
    {
        return Invoice::query()
            ->with('user', 'subscription.plan');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'paid' => round((float) Invoice::where('status', 'paid')->sum('total'), 2),
                'pending' => Invoice::where('status', 'pending')->count(),
                'refunded' => Invoice::where('status', 'refunded')->count(),
                'count' => Invoice::count(),
            ],
        ];
    }
}
