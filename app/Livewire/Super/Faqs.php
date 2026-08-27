<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Faqs extends ResourceComponent
{
    public string $sortField = 'sort_order';

    public string $sortDirection = 'asc';

    public function toggleActive(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $faq->update(['is_active' => ! $faq->is_active]);
        $this->notifySuccess('FAQ updated.');
    }

    public function deleteFaq(int $id): void
    {
        Faq::findOrFail($id)->delete();
        $this->notifySuccess('FAQ deleted.');
    }

    protected function title(): string
    {
        return 'FAQ Management';
    }

    protected function view(): string
    {
        return 'livewire.super.faqs';
    }

    protected function searchable(): array
    {
        return ['question', 'answer'];
    }

    protected function query(): Builder
    {
        return Faq::query();
    }

    protected function viewData(): array
    {
        return [
            'totals' => ['all' => Faq::count(), 'active' => Faq::where('is_active', true)->count()],
            'categories' => Faq::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
        ];
    }
}
