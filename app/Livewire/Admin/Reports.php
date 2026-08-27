<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use App\Models\BlogPost;
use App\Models\Media;
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
    #[Url(except: 'content')]
    public string $report = 'content';

    #[Url(except: 30)]
    public int $range = 30;

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->rows();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            if ($rows) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            }

            fclose($out);
        }, "report-{$this->report}-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function rows(): array
    {
        $from = now()->subDays($this->range);

        return match ($this->report) {
            'content' => BlogPost::with('user')->where('created_at', '>=', $from)->get()
                ->map(fn ($p) => ['title' => $p->title, 'author' => $p->user?->name, 'status' => $p->status, 'views' => $p->views_count, 'created' => $p->created_at->toDateString()])->all(),
            'websites' => Website::with('user')->where('created_at', '>=', $from)->get()
                ->map(fn ($w) => ['name' => $w->name, 'owner' => $w->user?->email, 'status' => $w->status->value, 'views' => $w->views_count])->all(),
            'users' => User::customers()->where('created_at', '>=', $from)->get()
                ->map(fn ($u) => ['name' => $u->name, 'email' => $u->email, 'status' => $u->status, 'joined' => $u->created_at->toDateString()])->all(),
            'support' => SupportTicket::with('user')->where('created_at', '>=', $from)->get()
                ->map(fn ($t) => ['ticket' => $t->ticket_number, 'subject' => $t->subject, 'status' => $t->status, 'priority' => $t->priority])->all(),
            default => [],
        };
    }

    public function render()
    {
        $from = now()->subDays($this->range - 1)->startOfDay();

        return view('livewire.admin.reports', [
            'reports' => [
                'content' => ['Content Report', 'book', 'Blog posts, pages and media'],
                'websites' => ['Website Report', 'globe', 'Sites created and published'],
                'users' => ['User Report', 'users', 'Signups and activity'],
                'support' => ['Support Report', 'ticket', 'Tickets and resolution'],
            ],
            'summary' => [
                'users' => User::customers()->where('created_at', '>=', $from)->count(),
                'websites' => Website::where('created_at', '>=', $from)->count(),
                'posts' => BlogPost::where('created_at', '>=', $from)->count(),
                'media' => Media::where('created_at', '>=', $from)->count(),
                'tickets' => SupportTicket::where('created_at', '>=', $from)->count(),
                'templates' => Template::count(),
            ],
            'rows' => array_slice($this->rows(), 0, 25),
        ])->layoutData($this->layoutData('Reports'));
    }
}
