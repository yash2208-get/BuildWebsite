<div class="space-y-6" x-data="{ createOpen: @entangle('showCreate') }">
    <x-ui.page-header title="Support" description="Get help from our team — we usually reply within a few hours.">
        <button x-on:click="createOpen = true" class="btn btn-primary">
            <x-icon name="plus" class="w-4 h-4" /> New ticket
        </button>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- ticket list --}}
        <div class="lg:col-span-1 space-y-4">
            <x-ui.card title="Your tickets" padding="p-0">
                @forelse($tickets as $t)
                    <button wire:click="$set('viewing', {{ $t->id }})" wire:key="tk-{{ $t->id }}"
                            @class(['w-full text-left px-5 py-3.5 border-b border-subtle last:border-0 transition-colors',
                                    'bg-brand-500/8' => $viewing === $t->id,
                                    'hover:bg-surface-muted' => $viewing !== $t->id])>
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium truncate flex-1">{{ $t->subject }}</p>
                            <x-ui.badge :color="$t->status->color()">{{ $t->status->label() }}</x-ui.badge>
                        </div>
                        <p class="text-[11px] text-tertiary mt-1 font-mono">{{ $t->ticket_number }}</p>
                        <div class="flex items-center gap-2.5 mt-1.5 text-[11px] text-tertiary">
                            <span>{{ $t->created_at->diffForHumans(short: true) }}</span>
                            @if($t->replies_count)<span class="flex items-center gap-1"><x-icon name="message" class="w-3 h-3" /> {{ $t->replies_count }}</span>@endif
                        </div>
                    </button>
                @empty
                    <x-ui.empty-state compact icon="ticket" title="No tickets yet" description="Open one whenever you need a hand." />
                @endforelse
            </x-ui.card>

            @if($tickets->hasPages())<div>{{ $tickets->links() }}</div>@endif

            <x-ui.card title="Common questions" padding="p-0">
                @foreach($faqs as $faq)
                    <details class="group border-b border-subtle last:border-0">
                        <summary class="px-5 py-3 text-sm font-medium cursor-pointer hover:bg-surface-muted transition-colors list-none flex items-center justify-between gap-2">
                            {{ $faq->question }}
                            <x-icon name="chevron-down" class="w-3.5 h-3.5 text-tertiary shrink-0 group-open:rotate-180 transition-transform" />
                        </summary>
                        <p class="px-5 pb-4 text-xs text-secondary leading-relaxed">{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </x-ui.card>
        </div>

        {{-- conversation --}}
        <div class="lg:col-span-2">
            @if($ticket)
                <x-ui.card padding="p-0">
                    <div class="px-6 py-4 border-b border-subtle">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-lg">{{ $ticket->subject }}</h2>
                                <div class="flex items-center gap-2.5 mt-1.5 flex-wrap">
                                    <span class="text-xs font-mono text-tertiary">{{ $ticket->ticket_number }}</span>
                                    <x-ui.badge :color="$ticket->status->color()" dot>{{ $ticket->status->label() }}</x-ui.badge>
                                    <x-ui.badge :color="match($ticket->priority) { 'urgent' => 'rose', 'high' => 'amber', 'medium' => 'sky', default => 'slate' }">
                                        {{ ucfirst($ticket->priority) }} priority
                                    </x-ui.badge>
                                </div>
                            </div>
                            @if(!in_array($ticket->status->value, ['closed', 'resolved']))
                                <button wire:click="closeTicket({{ $ticket->id }})" class="btn btn-ghost btn-sm shrink-0">Close ticket</button>
                            @endif
                        </div>
                    </div>

                    <div class="p-6 space-y-4 max-h-[26rem] overflow-y-auto scrollbar-thin">
                        {{-- original message --}}
                        <div class="flex gap-3">
                            <x-ui.avatar :name="$this->user()->name" size="sm" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-sm font-medium">You</span>
                                    <span class="text-[11px] text-tertiary">{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1.5 p-3.5 rounded-xl rounded-tl-sm bg-surface-muted text-sm leading-relaxed whitespace-pre-line">{{ $ticket->message }}</div>
                            </div>
                        </div>

                        @foreach($ticket->replies->where('is_internal', false) as $reply)
                            <div class="flex gap-3 {{ $reply->is_staff_reply ? '' : 'flex-row-reverse' }}">
                                <x-ui.avatar :name="$reply->user?->name ?? 'Support'" size="sm" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2 {{ $reply->is_staff_reply ? '' : 'justify-end' }}">
                                        <span class="text-sm font-medium">{{ $reply->is_staff_reply ? ($reply->user?->name ?? 'Support team') : 'You' }}</span>
                                        @if($reply->is_staff_reply)<x-ui.badge color="brand">Staff</x-ui.badge>@endif
                                        <span class="text-[11px] text-tertiary">{{ $reply->created_at->diffForHumans(short: true) }}</span>
                                    </div>
                                    <div @class(['mt-1.5 p-3.5 rounded-xl text-sm leading-relaxed whitespace-pre-line',
                                                 'rounded-tl-sm bg-brand-500/8 ring-1 ring-brand-500/12' => $reply->is_staff_reply,
                                                 'rounded-tr-sm bg-surface-muted' => !$reply->is_staff_reply])>{{ $reply->message }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($ticket->status->value !== 'closed')
                        <form wire:submit="sendReply" class="px-6 py-4 border-t border-subtle">
                            <textarea wire:model="reply" rows="3" class="field resize-none @error('reply') field-error @enderror" placeholder="Type your reply…"></textarea>
                            @error('reply')<p class="error-text">{{ $message }}</p>@enderror
                            <div class="flex justify-end mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <x-icon name="send" class="w-3.5 h-3.5" /> Send reply
                                </button>
                            </div>
                        </form>
                    @endif
                </x-ui.card>
            @else
                <x-ui.card class="h-full">
                    <x-ui.empty-state icon="message" title="Select a ticket"
                        description="Choose a conversation on the left, or start a new one.">
                        <button x-on:click="createOpen = true" class="btn btn-primary">
                            <x-icon name="plus" class="w-4 h-4" /> New ticket
                        </button>
                    </x-ui.empty-state>
                </x-ui.card>
            @endif
        </div>
    </div>

    <x-ui.modal show="createOpen" title="Open a support ticket" max-width="max-w-lg">
        <form wire:submit="createTicket" class="space-y-4">
            <div>
                <label class="label">Subject</label>
                <input wire:model="subject" type="text" class="field @error('subject') field-error @enderror" placeholder="Briefly describe the issue">
                @error('subject')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">Category</label>
                    <select wire:model="category" class="field">
                        @foreach($categories as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Priority</label>
                    <select wire:model="priority" class="field">
                        @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="label">Describe the issue</label>
                <textarea wire:model="message" rows="5" class="field @error('message') field-error @enderror"
                          placeholder="Include what you expected to happen, what actually happened, and any steps to reproduce it."></textarea>
                @error('message')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" class="btn btn-ghost" x-on:click="createOpen = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit ticket</button>
            </div>
        </form>
    </x-ui.modal>
</div>
