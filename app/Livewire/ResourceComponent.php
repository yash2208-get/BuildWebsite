<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Concerns\WithBulkActions;
use App\Livewire\Concerns\WithSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Base for every data-table screen in the admin and super-admin panels.
 *
 * Subclasses declare the query and the column set; searching, sorting,
 * filtering, pagination and bulk selection are handled here so individual
 * screens stay declarative.
 */
abstract class ResourceComponent extends BaseComponent
{
    use WithBulkActions, WithPagination, WithSorting;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 25)]
    public int $perPage = 25;

    /** @var array<string, string> */
    #[Url(except: [])]
    public array $filters = [];

    /** Human-readable page title. */
    abstract protected function title(): string;

    /** The base Eloquent query for this resource. */
    abstract protected function query(): Builder;

    /** Blade view rendered for this resource. */
    abstract protected function view(): string;

    /** Columns that free-text search should scan. */
    protected function searchable(): array
    {
        return ['name'];
    }

    /** Extra data merged into the view. */
    protected function viewData(): array
    {
        return [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'filters');
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search) || collect($this->filters)->filter(fn ($v) => filled($v) && $v !== 'all')->isNotEmpty();
    }

    protected function rows(): LengthAwarePaginator
    {
        $query = $this->query();

        if (filled($this->search) && $this->searchable()) {
            $query->where(function (Builder $q) {
                foreach ($this->searchable() as $i => $column) {
                    $method = $i === 0 ? 'where' : 'orWhere';

                    str_contains($column, '.')
                        ? $this->applyRelationSearch($q, $column, $i)
                        : $q->{$method}($column, 'like', '%'.$this->search.'%');
                }
            });
        }

        foreach ($this->filters as $key => $value) {
            if (blank($value) || $value === 'all') {
                continue;
            }

            $method = 'filter'.str($key)->studly()->toString();

            method_exists($this, $method)
                ? $this->{$method}($query, $value)
                : $query->where($key, $value);
        }

        return $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    private function applyRelationSearch(Builder $query, string $column, int $index): void
    {
        [$relation, $field] = explode('.', $column, 2);
        $method = $index === 0 ? 'whereHas' : 'orWhereHas';

        $query->{$method}($relation, fn (Builder $q) => $q->where($field, 'like', '%'.$this->search.'%'));
    }

    protected function selectableIds(): array
    {
        return $this->rows()->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function render()
    {
        return view($this->view(), array_merge([
            'rows' => $this->rows(),
        ], $this->viewData()))->layoutData($this->layoutData($this->title()));
    }
}
