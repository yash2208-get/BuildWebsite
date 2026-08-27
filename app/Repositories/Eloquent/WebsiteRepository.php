<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Website;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WebsiteRepository extends BaseRepository
{
    public function __construct(Website $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['user_id'] ?? null, fn (Builder $q, $v) => $q->where('user_id', $v))
            ->when($filters['search'] ?? null, fn (Builder $q, $v) => $q->search($v))
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(($filters['category'] ?? 'all') !== 'all', fn (Builder $q) => $q->where('category', $filters['category']))
            ->orderByDesc($filters['sort'] ?? 'updated_at');
    }

    public function paginateForUser(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->paginate($perPage, array_merge($filters, ['user_id' => $user->id]));
    }

    public function findBySubdomain(string $subdomain): ?Website
    {
        return $this->query()->where('subdomain', $subdomain)->first();
    }

    public function findByDomain(string $domain): ?Website
    {
        return $this->query()->where('custom_domain', $domain)->where('domain_status', 'verified')->first();
    }

    /** @return Collection<int, Website> */
    public function recentFor(User $user, int $limit = 5): Collection
    {
        return $this->query()
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    public function statsFor(?User $user = null): array
    {
        $base = fn () => $this->query()->when($user, fn (Builder $q) => $q->where('user_id', $user->id));

        return [
            'total' => (clone $base())->count(),
            'published' => (clone $base())->where('status', 'published')->count(),
            'draft' => (clone $base())->where('status', 'draft')->count(),
            'views' => (int) (clone $base())->sum('views_count'),
        ];
    }
}
