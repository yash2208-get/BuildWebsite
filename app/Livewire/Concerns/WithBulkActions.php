<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithBulkActions
{
    /** @var array<int, string> */
    public array $selected = [];

    public bool $selectAll = false;

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value ? $this->selectableIds() : [];
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function hasSelection(): bool
    {
        return count($this->selected) > 0;
    }

    /** @return array<int, string> */
    abstract protected function selectableIds(): array;
}
