<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\ResourceComponent;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Tickets extends ResourceComponent
{
    public ?int $viewing = null;

    public string $reply = '';

    public bool $internalNote = false;

    protected function title(): string
    {
        return 'Support Tickets';
    }

    protected function view(): string
    {
        return 'livewire.admin.tickets';
    }

    protected function searchable(): array
    {
        return ['subject', 'ticket_number', 'message', 'user.name', 'user.email'];
    }

    protected function query(): Builder
    {
        return SupportTicket::query()->with('user', 'assignee')->withCount('replies');
    }

    public function openTicket(int $id): void
    {
        $this->viewing = $id;
    }

    public function sendReply(): void
    {
        $ticket = SupportTicket::findOrFail($this->viewing);
        $this->authorize('reply', $ticket);

        $this->validate(['reply' => ['required', 'string', 'min:2', 'max:5000']]);

        TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $this->reply,
            'is_staff_reply' => true,
            'is_internal' => $this->internalNote,
        ]);

        if (! $this->internalNote && $ticket->status === 'open') {
            $ticket->update(['status' => 'pending', 'first_response_at' => $ticket->first_response_at ?? now()]);
        }

        $this->reset('reply', 'internalNote');
        $this->notifySuccess($this->internalNote ? 'Internal note added.' : 'Reply sent to the customer.');
    }

    public function setStatus(int $id, string $status): void
    {
        $ticket = SupportTicket::findOrFail($id);
        $this->authorize('update', $ticket);

        $ticket->update([
            'status' => $status,
            'resolved_at' => $status === 'resolved' ? now() : $ticket->resolved_at,
            'closed_at' => $status === 'closed' ? now() : null,
        ]);

        $this->notifySuccess("Ticket marked as {$status}.");
    }

    public function setPriority(int $id, string $priority): void
    {
        SupportTicket::findOrFail($id)->update(['priority' => $priority]);
        $this->notifySuccess('Priority updated.');
    }

    public function assign(int $id, ?int $userId): void
    {
        SupportTicket::findOrFail($id)->update(['assigned_to' => $userId]);
        $this->notifySuccess($userId ? 'Ticket assigned.' : 'Ticket unassigned.');
    }

    protected function viewData(): array
    {
        return [
            'ticket' => $this->viewing
                ? SupportTicket::with('user', 'assignee', 'replies.user')->find($this->viewing)
                : null,
            'staff' => User::staff()->orderBy('name')->get(['id', 'name']),
            'totals' => [
                'all' => SupportTicket::count(),
                'open' => SupportTicket::where('status', 'open')->count(),
                'pending' => SupportTicket::where('status', 'pending')->count(),
                'resolved' => SupportTicket::where('status', 'resolved')->count(),
                'urgent' => SupportTicket::where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
            ],
        ];
    }
}
