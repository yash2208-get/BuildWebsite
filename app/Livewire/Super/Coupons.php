<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Coupons extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function toggleActive(int $id): void
    {
        $c = Coupon::findOrFail($id);
        $c->update(['is_active' => ! $c->is_active]);
        $this->notifySuccess('Coupon updated.');
    }

    public function deleteCoupon(int $id): void
    {
        Coupon::findOrFail($id)->delete();
        $this->notifySuccess('Coupon deleted.');
    }

    protected function title(): string
    {
        return 'Coupons';
    }

    protected function view(): string
    {
        return 'livewire.super.coupons';
    }

    protected function searchable(): array
    {
        return ['code', 'description'];
    }

    protected function query(): Builder
    {
        return Coupon::query();
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => Coupon::count(),
                'active' => Coupon::where('is_active', true)->count(),
                'redemptions' => (int) Coupon::sum('redemptions_count'),
            ],
        ];
    }
}
