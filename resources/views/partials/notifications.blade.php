@php
    $notifications = auth()->user()?->unreadNotifications()->limit(6)->get() ?? collect();
@endphp
<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
    <button class="btn btn-ghost btn-icon relative" x-on:click="open = !open" aria-label="Notifications">
        <x-icon name="bell" class="w-[18px] h-[18px]" />
        @if($notifications->count())
            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-ink-900"></span>
        @endif
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.right
         class="absolute right-0 mt-2 w-80 card shadow-xl overflow-hidden z-50">
        <div class="px-4 py-3 border-b border-subtle flex items-center justify-between">
            <span class="font-semibold text-sm">Notifications</span>
            @if($notifications->count())<span class="badge bg-brand-500/12 text-brand-600 dark:text-brand-300">{{ $notifications->count() }} new</span>@endif
        </div>
        <div class="max-h-80 overflow-y-auto scroll-thin">
            @forelse($notifications as $n)
                <a href="{{ data_get($n->data, 'url', '#') }}" class="flex gap-3 px-4 py-3 hover:bg-surface-muted transition-colors border-b border-subtle last:border-0">
                    <span class="w-8 h-8 rounded-lg bg-brand-500/12 text-brand-500 grid place-items-center shrink-0">
                        <x-icon :name="data_get($n->data, 'icon', 'bell')" class="w-4 h-4" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-medium truncate">{{ data_get($n->data, 'title', 'Notification') }}</span>
                        <span class="block text-xs text-tertiary line-clamp-2">{{ data_get($n->data, 'message') }}</span>
                        <span class="block text-[11px] text-tertiary mt-0.5">{{ $n->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                <x-ui.empty-state compact icon="bell" title="You're all caught up"
                                  description="New activity will appear here." />
            @endforelse
        </div>
    </div>
</div>
