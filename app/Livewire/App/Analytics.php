<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Page;
use App\Models\Website;
use App\Models\WebsiteAnalytic;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Analytics extends BaseComponent
{
    #[Url(except: 30)]
    public int $range = 30;

    #[Url(except: 'all')]
    public string $websiteId = 'all';

    private function websiteIds(): array
    {
        return $this->websiteId === 'all'
            ? $this->user()->websites()->pluck('id')->all()
            : [(int) $this->websiteId];
    }

    public function render()
    {
        $ids = $this->websiteIds();
        $from = now()->subDays($this->range - 1)->startOfDay();

        $daily = WebsiteAnalytic::query()
            ->whereIn('website_id', $ids)
            ->where('date', '>=', $from)
            ->selectRaw('date, SUM(views) as views, SUM(unique_visitors) as visitors, AVG(bounce_rate) as bounce_rate')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $labels = $views = $visitors = [];

        for ($i = $this->range - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format($this->range > 60 ? 'M j' : 'M j');
            $views[] = (int) ($daily[$key]->views ?? 0);
            $visitors[] = (int) ($daily[$key]->visitors ?? 0);
        }

        $totalViews = array_sum($views);
        $totalVisitors = array_sum($visitors);
        $avgBounce = round((float) $daily->avg('bounce_rate'), 1);

        // Previous period for the delta badge.
        $prevViews = (int) WebsiteAnalytic::whereIn('website_id', $ids)
            ->whereBetween('date', [now()->subDays($this->range * 2 - 1)->startOfDay(), $from->copy()->subSecond()])
            ->sum('views');

        $delta = $prevViews > 0 ? round((($totalViews - $prevViews) / $prevViews) * 100, 1) : null;

        $topPages = Page::query()
            ->whereIn('website_id', $ids)
            ->orderByDesc('views_count')
            ->limit(8)
            ->get(['id', 'website_id', 'title', 'slug', 'views_count']);

        $topSites = Website::query()
            ->whereIn('id', $ids)
            ->orderByDesc('views_count')
            ->limit(6)
            ->get(['id', 'name', 'subdomain', 'views_count', 'status']);

        return view('livewire.app.analytics', [
            'labels' => $labels,
            'views' => $views,
            'visitors' => $visitors,
            'totalViews' => $totalViews,
            'totalVisitors' => $totalVisitors,
            'delta' => $delta,
            'avgDaily' => $this->range ? (int) round($totalViews / $this->range) : 0,
            'bounceRate' => $avgBounce,
            'topPages' => $topPages,
            'topSites' => $topSites,
            'websites' => $this->user()->websites()->orderBy('name')->get(['id', 'name']),
            'devices' => [
                ['label' => 'Desktop', 'value' => 58, 'color' => '#6366f1', 'icon' => 'monitor'],
                ['label' => 'Mobile', 'value' => 34, 'color' => '#22d3ee', 'icon' => 'phone'],
                ['label' => 'Tablet', 'value' => 8, 'color' => '#a78bfa', 'icon' => 'tablet'],
            ],
            'sources' => [
                ['label' => 'Direct', 'value' => 42], ['label' => 'Organic search', 'value' => 28],
                ['label' => 'Social', 'value' => 18], ['label' => 'Referral', 'value' => 12],
            ],
        ])->layoutData($this->layoutData('Analytics'));
    }
}
