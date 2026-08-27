<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithSorting
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function sortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'chevron-down';
        }

        return $this->sortDirection === 'asc' ? 'arrow-up' : 'arrow-down';
    }
}
