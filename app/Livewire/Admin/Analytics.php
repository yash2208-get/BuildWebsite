<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use App\Models\Page;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteAnalytic;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Analytics extends BaseComponent
{
    #[Url(except: 30)]
    public int $range = 30;

    public function render()
    {
        $from = now()->subDays($this->range - 1)->startOfDay();

        $rows = WebsiteAnalytic::where('date', '>=', $from)
            ->selectRaw('date, SUM(views) as views, SUM(unique_visitors) as visitors')
            ->groupBy('date')->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $labels = $views = $visitors = [];

        for ($i = $this->range - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $k = $d->toDateString();
            $labels[] = $d->format('M j');
            $views[] = (int) ($rows[$k]->views ?? 0);
            $visitors[] = (int) ($rows[$k]->visitors ?? 0);
        }

        $signups = User::customers()->where('created_at', '>=', $from)->get(['created_at'])
            ->groupBy(fn ($u) => $u->created_at->toDateString())->map->count();

        $signupSeries = [];
        for ($i = $this->range - 1; $i >= 0; $i--) {
            $signupSeries[] = $signups[now()->subDays($i)->toDateString()] ?? 0;
        }

        return view('livewire.admin.analytics', [
            'labels' => $labels,
            'views' => $views,
            'visitors' => $visitors,
            'signupSeries' => $signupSeries,
            'stats' => [
                'views' => array_sum($views),
                'visitors' => array_sum($visitors),
                'signups' => array_sum($signupSeries),
                'avgDaily' => (int) round(array_sum($views) / max($this->range, 1)),
                'websites' => Website::count(),
                'published' => Website::where('status', 'published')->count(),
            ],
            'topWebsites' => Website::with('user')->orderByDesc('views_count')->limit(10)->get(),
            'topPages' => Page::with('website')->orderByDesc('views_count')->limit(10)->get(),
            'byCategory' => Website::selectRaw('category, COUNT(*) as total, SUM(views_count) as views')
                ->groupBy('category')->orderByDesc('total')->limit(8)->get(),
        ])->layoutData($this->layoutData('Analytics'));
    }
}
