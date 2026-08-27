<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Faq;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Support extends BaseComponent
{
    use WithPagination;

    public bool $showCreate = false;

    public ?int $viewing = null;

    public string $subject = '';
    public string $category = 'general';
    public string $priority = 'medium';
    public string $message = '';
    public string $reply = '';

    public function createTicket(): void
    {
        $data = $this->validate([
            'subject' => ['required', 'string', 'min:4', 'max:190'],
            'category' => ['required', 'string', 'max:40'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $this->user()->id,
            'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'message' => $data['message'],
            'status' => 'open',
        ]);

        $this->reset('showCreate', 'subject', 'message');
        $this->viewing = $ticket->id;
        $this->notifySuccess("Ticket {$ticket->ticket_number} created — we typically reply within a few hours.");
    }

    public function sendReply(): void
    {
        $ticket = SupportTicket::where('user_id', $this->user()->id)->findOrFail($this->viewing);
        $this->authorize('reply', $ticket);

        $this->validate(['reply' => ['required', 'string', 'min:2', 'max:5000']]);

        TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $this->user()->id,
            'message' => $this->reply,
            'is_staff_reply' => false,
        ]);

        if ($ticket->status->value === 'resolved') {
            $ticket->update(['status' => 'open']);
        }

        $this->reset('reply');
        $this->notifySuccess('Reply sent.');
    }

    public function closeTicket(int $id): void
    {
        $ticket = SupportTicket::where('user_id', $this->user()->id)->findOrFail($id);
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        $this->notifySuccess('Ticket closed. Thanks for letting us know!');
    }

    public function render()
    {
        return view('livewire.app.support', [
            'tickets' => SupportTicket::where('user_id', $this->user()->id)
                ->withCount('replies')->latest()->paginate(10),
            'ticket' => $this->viewing
                ? SupportTicket::with('replies.user')->where('user_id', $this->user()->id)->find($this->viewing)
                : null,
            'faqs' => Faq::active()->limit(6)->get(),
            'categories' => ['general' => 'General question', 'technical' => 'Technical issue', 'billing' => 'Billing & plans', 'bug' => 'Bug report', 'feature' => 'Feature request'],
        ])->layoutData($this->layoutData('Support'));
    }
}
