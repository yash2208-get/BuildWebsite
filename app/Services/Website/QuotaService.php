<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Models\User;
use App\Models\Website;
use App\Support\Exceptions\QuotaExceededException;

/**
 * Central authority for plan limits. Every creation path funnels through here
 * so quota rules live in exactly one place.
 */
class QuotaService
{
    public function limits(User $user): array
    {
        $plan = $user->activePlan();

        return [
            'max_websites' => $plan?->max_websites ?? config('platform.free_quota.max_websites', 3),
            'max_pages' => $plan?->max_pages ?? config('platform.free_quota.max_pages', 10),
            'max_storage_mb' => $plan?->max_storage_mb ?? config('platform.free_quota.max_storage_mb', 250),
            'max_ai_credits' => $plan?->max_ai_credits ?? config('platform.free_quota.max_ai_credits', 25),
            'allows_custom_domain' => (bool) ($plan?->allows_custom_domain ?? false),
            'allows_export' => (bool) ($plan?->allows_export ?? false),
            'allows_remove_branding' => (bool) ($plan?->allows_remove_branding ?? false),
        ];
    }

    public function usage(User $user): array
    {
        $limits = $this->limits($user);

        return [
            'websites' => [
                'used' => $user->websites()->count(),
                'limit' => $limits['max_websites'],
            ],
            'storage' => [
                'used' => $user->storageUsedMb(),
                'limit' => $limits['max_storage_mb'],
            ],
            'ai_credits' => [
                'used' => $user->aiCreditsUsed(),
                'limit' => $limits['max_ai_credits'],
            ],
        ];
    }

    public function percentage(int|float $used, int $limit): int
    {
        if ($limit < 0) {
            return 0;
        }

        if ($limit === 0) {
            return 100;
        }

        return (int) min(100, round(($used / $limit) * 100));
    }

    public function canCreateWebsite(User $user): bool
    {
        $limit = $this->limits($user)['max_websites'];

        return $limit < 0 || $user->websites()->count() < $limit;
    }

    /** @throws QuotaExceededException */
    public function assertCanCreateWebsite(User $user): void
    {
        if (! $this->canCreateWebsite($user)) {
            $limit = $this->limits($user)['max_websites'];

            throw new QuotaExceededException(
                "Your plan includes {$limit} website(s). Upgrade to create more."
            );
        }
    }

    public function canCreatePage(Website $website): bool
    {
        $limit = $this->limits($website->user)['max_pages'];

        return $limit < 0 || $website->pages()->count() < $limit;
    }

    /** @throws QuotaExceededException */
    public function assertCanCreatePage(Website $website): void
    {
        if (! $this->canCreatePage($website)) {
            $limit = $this->limits($website->user)['max_pages'];

            throw new QuotaExceededException(
                "Your plan allows {$limit} pages per website. Upgrade to add more."
            );
        }
    }

    public function canUpload(User $user, int $bytes): bool
    {
        $limit = $this->limits($user)['max_storage_mb'];

        if ($limit < 0) {
            return true;
        }

        return ($user->storageUsedMb() + ($bytes / 1048576)) <= $limit;
    }

    /** @throws QuotaExceededException */
    public function assertCanUpload(User $user, int $bytes): void
    {
        if (! $this->canUpload($user, $bytes)) {
            throw new QuotaExceededException('You have reached your storage limit. Upgrade or remove unused media.');
        }
    }
}
