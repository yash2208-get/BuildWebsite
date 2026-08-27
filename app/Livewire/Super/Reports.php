<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\AiGeneration;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class Reports extends BaseComponent
{
    #[Url(except: 'growth')]
    public string $report = 'growth';

    #[Url(except: 90)]
    public int $range = 90;

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->reportRows();
        $name = "aurorabuild-{$this->report}-".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            if ($rows) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }

    private function reportRows(): array
    {
        $from = now()->subDays($this->range);

        return match ($this->report) {
            'growth' => User::customers()->where('created_at', '>=', $from)
                ->get()->map(fn ($u) => [
                    'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
                    'status' => $u->status, 'signed_up' => $u->created_at->toDateString(),
                ])->all(),

            'websites' => Website::with('user')->where('created_at', '>=', $from)
                ->get()->map(fn ($w) => [
                    'id' => $w->id, 'name' => $w->name, 'owner' => $w->user?->email,
                    'status' => $w->status->value, 'views' => $w->views_count,
                    'created' => $w->created_at->toDateString(),
                ])->all(),

            'revenue' => Invoice::with('user')->where('status', 'paid')->where('paid_at', '>=', $from)
                ->get()->map(fn ($i) => [
                    'invoice' => $i->invoice_number, 'customer' => $i->user?->email,
                    'total' => $i->total, 'currency' => $i->currency,
                    'paid_at' => $i->paid_at?->toDateString(),
                ])->all(),

            'ai' => AiGeneration::with('user')->where('created_at', '>=', $from)
                ->get()->map(fn ($g) => [
                    'id' => $g->id, 'user' => $g->user?->email, 'type' => $g->type->value,
                    'status' => $g->status, 'credits' => $g->credits_used,
                    'created' => $g->created_at->toDateString(),
                ])->all(),

            'support' => SupportTicket::with('user')->where('created_at', '>=', $from)
                ->get()->map(fn ($t) => [
                    'ticket' => $t->ticket_number, 'customer' => $t->user?->email,
                    'subject' => $t->subject, 'priority' => $t->priority,
                    'status' => $t->status, 'created' => $t->created_at->toDateString(),
                ])->all(),

            default => [],
        };
    }

    public function render()
    {
        $from = now()->subDays($this->range - 1)->startOfDay();

        $labels = $signups = $sites = [];
        $userRows = User::customers()->where('created_at', '>=', $from)->get(['created_at'])
            ->groupBy(fn ($u) => $u->created_at->toDateString())->map->count();
        $siteRows = Website::where('created_at', '>=', $from)->get(['created_at'])
            ->groupBy(fn ($w) => $w->created_at->toDateString())->map->count();

        for ($i = $this->range - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $labels[] = $d->format('M j');
            $signups[] = $userRows[$d->toDateString()] ?? 0;
            $sites[] = $siteRows[$d->toDateString()] ?? 0;
        }

        return view('livewire.super.reports', [
            'reports' => [
                'growth' => ['User Growth', 'users', 'New signups and account activity'],
                'websites' => ['Website Report', 'globe', 'Sites created, published and traffic'],
                'revenue' => ['Revenue Report', 'dollar', 'Invoices, MRR and plan performance'],
                'ai' => ['AI Usage Report', 'sparkles', 'Generations, credits and success rate'],
                'support' => ['Support Report', 'ticket', 'Tickets, response times and resolution'],
            ],
            'labels' => $labels,
            'signups' => $signups,
            'sites' => $sites,
            'summary' => [
                'users' => User::customers()->where('created_at', '>=', $from)->count(),
                'websites' => Website::where('created_at', '>=', $from)->count(),
                'revenue' => round((float) Invoice::where('status', 'paid')->where('paid_at', '>=', $from)->sum('total'), 2),
                'ai' => AiGeneration::where('created_at', '>=', $from)->count(),
                'tickets' => SupportTicket::where('created_at', '>=', $from)->count(),
                'templates' => Template::count(),
            ],
            'rows' => array_slice($this->reportRows(), 0, 25),
        ])->layoutData($this->layoutData('Reports'));
    }
}
