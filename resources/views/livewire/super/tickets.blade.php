<div class="space-y-6">
    <x-ui.page-header title="Support Tickets" description="Respond to customer requests and keep the queue moving." />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.stat label="All tickets" :value="$totals['all']" icon="ticket" color="brand" />
        <x-ui.stat label="Open" :value="$totals['open']" icon="inbox" color="sky" />
        <x-ui.stat label="Pending" :value="$totals['pending']" icon="clock" color="amber" />
        <x-ui.stat label="Resolved" :value="$totals['resolved']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Urgent" :value="$totals['urgent']" icon="alert" color="rose" />
    </div>

    <x-ui.toolbar placeholder="Search by subject, reference or customer…">
        <select wire:model.live="filters.status" class="field lg:w-40 shrink-0">
            <option value="">All statuses</option>
            @foreach(['open' => 'Open', 'pending' => 'Pending', 'answered' => 'Answered', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </select>
        <select wire:model.live="filters.priority" class="field lg:w-40 shrink-0">
            <option value="">All priorities</option>
            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No tickets found" emptyIcon="ticket" emptyDescription="Nothing in the queue right now.">
        <x-slot:head>
            <x-ui.th sort="reference" :sortIcon="$this->sortIcon('reference')">Ref</x-ui.th>
            <x-ui.th sort="subject" :sortIcon="$this->sortIcon('subject')">Subject</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th sort="priority" :sortIcon="$this->sortIcon('priority')">Priority</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th>Assignee</x-ui.th>
            <x-ui.th sort="last_reply_at" :sortIcon="$this->sortIcon('last_reply_at')">Last activity</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="t-{{ $row->id }}" class="cursor-pointer" wire:click="openTicket({{ $row->id }})">
                    <td><span class="font-mono text-xs text-tertiary">{{ $row->reference }}</span></td>
                    <td>
                        <div class="min-w-0">
                            <p class="font-medium truncate max-w-xs">{{ $row->subject }}</p>
                            <p class="text-xs text-tertiary">{{ $row->replies_count }} {{ Str::plural('reply', $row->replies_count) }} · {{ ucfirst($row->category ?: 'general') }}</p>
                        </div>
                    </td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td>
                        <x-ui.badge :color="match($row->priority) { 'urgent' => 'rose', 'high' => 'amber', 'medium' => 'sky', default => 'slate' }" dot>
                            {{ ucfirst($row->priority) }}
                        </x-ui.badge>
                    </td>
                    <td><x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge></td>
                    <td>
                        @if($row->assignee)
                            <x-ui.avatar :name="$row->assignee->name" size="xs" />
                        @else
                            <span class="text-xs text-tertiary">Unassigned</span>
                        @endif
                    </td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ ($row->last_reply_at ?? $row->created_at)->diffForHumans(short: true) }}</span></td>
                    <td class="text-right">
                        <button wire:click.stop="openTicket({{ $row->id }})" class="btn btn-ghost btn-sm">Open</button>
                    </td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

    {{-- ─────────────── Conversation drawer ─────────────── --}}
    @if($ticket)
        <div class="fixed inset-0 z-[70] flex justify-end" x-data x-on:keydown.escape.window="$wire.set('viewing', null)">
            <div class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm" wire:click="$set('viewing', null)"></div>

            <aside class="relative w-full max-w-2xl h-full bg-surface border-l border-subtle shadow-2xl flex flex-col animate-[slide-left_.28s_cubic-bezier(.16,1,.3,1)_both]">
                <header class="px-6 py-5 border-b border-subtle shrink-0">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-mono text-xs text-tertiary">{{ $ticket->reference }}</p>
                            <h2 class="text-lg font-semibold mt-1 leading-snug">{{ $ticket->subject }}</h2>
                            <div class="flex items-center gap-2 mt-2 text-xs text-tertiary">
                                <x-ui.avatar :name="$ticket->user?->name ?? 'Guest'" size="xs" />
                                <span>{{ $ticket->user?->name }}</span>
                                <span>·</span>
                                <span>{{ $ticket->created_at->format('M j, Y \a\t g:ia') }}</span>
                            </div>
                        </div>
                        <button wire:click="$set('viewing', null)" class="btn btn-ghost btn-icon shrink-0">
                            <x-icon name="x" class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-4">
                        <select wire:change="setStatus({{ $ticket->id }}, $event.target.value)" class="field field-sm w-auto">
                            @foreach(['open' => 'Open', 'pending' => 'Pending', 'answered' => 'Answered', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $v => $l)
                                <option value="{{ $v }}" @selected($ticket->status->value === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <select wire:change="setPriority({{ $ticket->id }}, $event.target.value)" class="field field-sm w-auto">
                            @foreach(['low' => 'Low priority', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $v => $l)
                                <option value="{{ $v }}" @selected($ticket->priority === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <select wire:change="assign({{ $ticket->id }}, $event.target.value)" class="field field-sm w-auto">
                            <option value="">Unassigned</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" @selected($ticket->assigned_to === $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </header>

                <div class="flex-1 overflow-y-auto scrollbar-thin px-6 py-5 space-y-4">
                    {{-- opening message --}}
                    <div class="flex gap-3">
                        <x-ui.avatar :name="$ticket->user?->name ?? 'Guest'" size="sm" class="shrink-0 mt-0.5" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline gap-2">
                                <span class="text-sm font-medium">{{ $ticket->user?->name }}</span>
                                <span class="text-xs text-tertiary">{{ $ticket->created_at->diffForHumans(short: true) }}</span>
                            </div>
                            <div class="mt-1.5 rounded-2xl rounded-tl-sm bg-surface-muted px-4 py-3 text-sm leading-relaxed whitespace-pre-line">{{ $ticket->message }}</div>
                        </div>
                    </div>

                    @foreach($ticket->replies as $r)
                        <div @class(['flex gap-3', 'flex-row-reverse' => $r->is_staff_reply])>
                            <x-ui.avatar :name="$r->user?->name ?? 'Staff'" size="sm" class="shrink-0 mt-0.5" />
                            <div class="min-w-0 flex-1 @if($r->is_staff_reply) flex flex-col items-end @endif">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-sm font-medium">{{ $r->user?->name }}</span>
                                    @if($r->is_internal_note)
                                        <x-ui.badge color="amber">Internal note</x-ui.badge>
                                    @endif
                                    <span class="text-xs text-tertiary">{{ $r->created_at->diffForHumans(short: true) }}</span>
                                </div>
                                <div @class([
                                    'mt-1.5 rounded-2xl px-4 py-3 text-sm leading-relaxed whitespace-pre-line max-w-lg',
                                    'bg-amber-500/10 ring-1 ring-amber-500/25 rounded-tr-sm' => $r->is_internal_note,
                                    'bg-brand-500 text-white rounded-tr-sm' => $r->is_staff_reply && !$r->is_internal_note,
                                    'bg-surface-muted rounded-tl-sm' => !$r->is_staff_reply,
                                ])>{{ $r->message }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <footer class="border-t border-subtle p-4 shrink-0 bg-surface">
                    <form wire:submit="sendReply" class="space-y-3">
                        <textarea wire:model="reply" rows="3" class="field resize-none @error('reply') field-error @enderror"
                                  placeholder="Write your reply…"></textarea>
                        @error('reply')<p class="error-text">{{ $message }}</p>@enderror
                        <div class="flex items-center justify-between gap-3">
                            <label class="flex items-center gap-2 text-xs text-secondary cursor-pointer select-none">
                                <input type="checkbox" wire:model="internalNote" class="rounded border-subtle text-amber-500 focus:ring-amber-500/30 w-4 h-4">
                                Internal note (not visible to customer)
                            </label>
                            <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="sendReply">
                                <x-icon name="send" class="w-3.5 h-3.5" />
                                <span wire:loading.remove wire:target="sendReply">Send reply</span>
                                <span wire:loading wire:target="sendReply">Sending…</span>
                            </button>
                        </div>
                    </form>
                </footer>
            </aside>
        </div>
    @endif
</div>
