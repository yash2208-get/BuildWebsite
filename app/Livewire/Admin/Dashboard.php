<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\SupportTicket;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteAnalytic;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Dashboard extends BaseComponent
{
    #[Url(except: 30)]
    public int $range = 30;

    public function render()
    {
        $from = now()->subDays($this->range - 1)->startOfDay();

        $daily = WebsiteAnalytic::where('date', '>=', $from)
            ->selectRaw('date, SUM(views) as views')
            ->groupBy('date')->orderBy('date')->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $labels = $series = [];
        for ($i = $this->range - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $labels[] = $d->format('M j');
            $series[] = (int) ($daily[$d->toDateString()]->views ?? 0);
        }

        $newUsers = User::customers()->where('created_at', '>=', $from)->count();
        $prevUsers = User::customers()->whereBetween('created_at', [
            now()->subDays($this->range * 2)->startOfDay(), $from,
        ])->count();

        return view('livewire.admin.dashboard', [
            'stats' => [
                'users' => User::customers()->count(),
                'websites' => Website::count(),
                'published' => Website::where('status', 'published')->count(),
                'templates' => Template::count(),
                'media' => Media::count(),
                'posts' => BlogPost::count(),
                'openTickets' => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
                'newUsers' => $newUsers,
                'userDelta' => $prevUsers > 0 ? round((($newUsers - $prevUsers) / $prevUsers) * 100, 1) : null,
            ],
            'labels' => $labels,
            'series' => $series,
            'totalViews' => array_sum($series),
            'recentUsers' => User::customers()->latest()->limit(6)->get(),
            'recentWebsites' => Website::with('user')->latest()->limit(6)->get(),
            'tickets' => SupportTicket::with('user')->whereIn('status', ['open', 'pending'])->latest()->limit(5)->get(),
            'activity' => ActivityLog::with('user')->latest()->limit(8)->get(),
            'topSites' => Website::orderByDesc('views_count')->limit(5)->get(['id', 'name', 'views_count', 'status']),
        ])->layoutData($this->layoutData('Admin Dashboard'));
    }
}
