<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Revenue extends BaseComponent
{
    #[Url(except: 12)]
    public int $months = 12;

    public function render()
    {
        $from = now()->subMonths($this->months - 1)->startOfMonth();

        $monthly = Invoice::where('status', 'paid')
            ->where('paid_at', '>=', $from)
            ->get(['total', 'paid_at'])
            ->groupBy(fn ($i) => Carbon::parse($i->paid_at)->format('Y-m'))
            ->map(fn ($g) => round((float) $g->sum('total'), 2));

        $labels = $series = [];

        for ($i = $this->months - 1; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $labels[] = $m->format('M Y');
            $series[] = $monthly[$m->format('Y-m')] ?? 0;
        }

        $mrr = round((float) Subscription::where('status', 'active')
            ->selectRaw("SUM(CASE WHEN billing_cycle = 'yearly' THEN amount / 12 ELSE amount END) as v")
            ->value('v'), 2);

        $activeSubs = Subscription::where('status', 'active')->count();
        $cancelled = Subscription::where('status', 'canceled')->count();
        $customers = User::customers()->count();

        $thisMonth = $series[count($series) - 1] ?? 0;
        $lastMonth = $series[count($series) - 2] ?? 0;

        return view('livewire.super.revenue', [
            'labels' => $labels,
            'series' => $series,
            'metrics' => [
                'total' => round((float) Invoice::where('status', 'paid')->sum('total'), 2),
                'thisMonth' => $thisMonth,
                'lastMonth' => $lastMonth,
                'growth' => $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : null,
                'mrr' => $mrr,
                'arr' => round($mrr * 12, 2),
                'arpu' => $customers > 0 ? round($mrr / $customers, 2) : 0,
                'ltv' => $activeSubs > 0 ? round(($mrr / max($activeSubs, 1)) * 24, 2) : 0,
                'churn' => ($activeSubs + $cancelled) > 0 ? round(($cancelled / ($activeSubs + $cancelled)) * 100, 1) : 0,
                'activeSubs' => $activeSubs,
                'refunded' => round((float) Invoice::where('status', 'refunded')->sum('total'), 2),
                'pending' => round((float) Invoice::where('status', 'pending')->sum('total'), 2),
                'avgInvoice' => round((float) Invoice::where('status', 'paid')->avg('total'), 2),
            ],
            'byPlan' => Plan::query()
                ->leftJoin('subscriptions', function ($join) {
                    $join->on('subscriptions.plan_id', '=', 'plans.id')->where('subscriptions.status', '=', 'active');
                })
                ->selectRaw('plans.id, plans.name, plans.slug, plans.price_monthly, COUNT(subscriptions.id) as subscribers, COALESCE(SUM(subscriptions.amount), 0) as revenue')
                ->groupBy('plans.id', 'plans.name', 'plans.slug', 'plans.price_monthly')
                ->orderByDesc('revenue')->get(),
            'topCustomers' => Invoice::where('status', 'paid')
                ->selectRaw('user_id, SUM(total) as spent, COUNT(*) as invoices')
                ->groupBy('user_id')->orderByDesc('spent')->limit(8)
                ->with('user')->get(),
            'recentInvoices' => Invoice::with('user', 'subscription.plan')->latest()->limit(10)->get(),
        ])->layoutData($this->layoutData('Revenue Analytics'));
    }
}
