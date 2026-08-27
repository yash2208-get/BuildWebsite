<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\AiGeneration;
use App\Models\WebsiteAnalytic;
use App\Repositories\Eloquent\WebsiteRepository;
use App\Services\Website\QuotaService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends BaseComponent
{
    public string $range = '30';

    public function updatedRange(): void
    {
        unset($this->chart);
    }

    public function render()
    {
        $user = $this->user();
        $repo = app(WebsiteRepository::class);
        $quotas = app(QuotaService::class);

        $websiteIds = $user->websites()->pluck('id');
        $days = (int) $this->range;

        $analytics = WebsiteAnalytic::query()
            ->whereIn('website_id', $websiteIds)
            ->where('date', '>=', now()->subDays($days)->toDateString())
            ->selectRaw('date, SUM(views) as views, SUM(unique_visitors) as visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $previous = (int) WebsiteAnalytic::query()
            ->whereIn('website_id', $websiteIds)
            ->whereBetween('date', [now()->subDays($days * 2)->toDateString(), now()->subDays($days)->toDateString()])
            ->sum('views');

        $current = (int) $analytics->sum('views');
        $delta = $previous > 0 ? round((($current - $previous) / $previous) * 100) : 0;

        return view('livewire.app.dashboard', [
            'stats' => $repo->statsFor($user),
            'usage' => $quotas->usage($user),
            'recent' => $repo->recentFor($user, 6),
            'views' => $current,
            'visitors' => (int) $analytics->sum('visitors'),
            'delta' => $delta,
            'chartLabels' => $analytics->pluck('date')->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('M j'))->values(),
            'chartViews' => $analytics->pluck('views')->values(),
            'chartVisitors' => $analytics->pluck('visitors')->values(),
            'aiRecent' => AiGeneration::where('user_id', $user->id)->latest()->limit(5)->get(),
            'aiCount' => AiGeneration::where('user_id', $user->id)->whereMonth('created_at', now()->month)->count(),
        ])->layoutData($this->layoutData('Dashboard'));
    }
}
