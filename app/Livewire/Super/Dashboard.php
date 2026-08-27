<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\ActivityLog;
use App\Models\AiGeneration;
use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\SecurityLog;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteAnalytic;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        // ── revenue series ────────────────────────────────────────────
        $revenueRows = Invoice::where('status', 'paid')
            ->where('paid_at', '>=', $from)
            ->selectRaw('DATE(paid_at) as d, SUM(total) as amount')
            ->groupBy('d')->pluck('amount', 'd');

        $trafficRows = WebsiteAnalytic::where('date', '>=', $from)
            ->selectRaw('date, SUM(views) as views')
            ->groupBy('date')->get()
            ->mapWithKeys(fn ($r) => [Carbon::parse($r->date)->toDateString() => (int) $r->views]);

        $signupRows = User::customers()->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd');

        $labels = $revenue = $traffic = $signups = [];

        for ($i = $this->range - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('M j');
            $revenue[] = round((float) ($revenueRows[$key] ?? 0), 2);
            $traffic[] = $trafficRows[$key] ?? 0;
            $signups[] = (int) ($signupRows[$key] ?? 0);
        }

        // ── headline figures ──────────────────────────────────────────
        $totalRevenue = round((float) Invoice::where('status', 'paid')->sum('total'), 2);
        $periodRevenue = round(array_sum($revenue), 2);

        $prevRevenue = round((float) Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [now()->subDays($this->range * 2 - 1)->startOfDay(), $from])
            ->sum('total'), 2);

        $mrr = round((float) Subscription::where('status', 'active')
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->selectRaw("SUM(CASE WHEN subscriptions.billing_cycle = 'yearly' THEN subscriptions.amount / 12 ELSE subscriptions.amount END) as mrr")
            ->value('mrr'), 2);

        $activeSubs = Subscription::where('status', 'active')->count();
        $totalCustomers = User::customers()->count();

        return view('livewire.super.dashboard', [
            'labels' => $labels,
            'revenueSeries' => $revenue,
            'trafficSeries' => $traffic,
            'signupSeries' => $signups,
            'stats' => [
                'revenue' => $totalRevenue,
                'periodRevenue' => $periodRevenue,
                'revenueDelta' => $prevRevenue > 0 ? round((($periodRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : null,
                'mrr' => $mrr,
                'arr' => round($mrr * 12, 2),
                'users' => $totalCustomers,
                'newUsers' => array_sum($signups),
                'websites' => Website::count(),
                'published' => Website::where('status', 'published')->count(),
                'subscriptions' => $activeSubs,
                'arpu' => $totalCustomers > 0 ? round($mrr / $totalCustomers, 2) : 0,
                'conversion' => $totalCustomers > 0 ? round(($activeSubs / $totalCustomers) * 100, 1) : 0,
                'aiGenerations' => AiGeneration::count(),
                'aiCredits' => (int) AiGeneration::sum('credits_used'),
                'views' => (int) Website::sum('views_count'),
                'openTickets' => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
                'unreadContacts' => ContactMessage::where('status', 'unread')->count(),
            ],
            'planBreakdown' => Subscription::where('subscriptions.status', 'active')
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->selectRaw('plans.name, plans.slug, COUNT(*) as total, SUM(subscriptions.amount) as revenue')
                ->groupBy('plans.id', 'plans.name', 'plans.slug')
                ->orderByDesc('total')->get(),
            'recentUsers' => User::customers()->latest()->limit(5)->get(),
            'recentInvoices' => Invoice::with('user')->where('status', 'paid')->latest('paid_at')->limit(5)->get(),
            'activity' => ActivityLog::with('user')->latest()->limit(8)->get(),
            'security' => SecurityLog::latest()->limit(5)->get(),
            'topWebsites' => Website::with('user')->orderByDesc('views_count')->limit(5)->get(),
        ])->layoutData($this->layoutData('Platform Overview'));
    }
}
