<div class="space-y-6">
    <x-ui.page-header title="Admin Dashboard" :description="'Content and community overview · '.now()->format('l, F j')">
        <div class="flex gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
            @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $d => $l)
                <button wire:click="$set('range', {{ $d }})"
                        @class(['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all',
                                'bg-surface shadow-sm' => $range === $d, 'text-tertiary hover:text-secondary' => $range !== $d])>{{ $l }}</button>
            @endforeach
        </div>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total users" :value="number_format($stats['users'])" icon="users" color="brand" :trend="$stats['userDelta']" :href="route('admin.users')" />
        <x-ui.stat label="Websites" :value="number_format($stats['websites'])" icon="globe" color="violet" :href="route('admin.websites')" />
        <x-ui.stat label="Published" :value="number_format($stats['published'])" icon="rocket" color="emerald" />
        <x-ui.stat label="Open tickets" :value="number_format($stats['openTickets'])" icon="ticket" color="amber" :href="route('admin.tickets')" />
    </div>

    <x-ui.card title="Platform traffic" :subtitle="number_format($totalViews).' views in the last '.$range.' days'">
        <div class="h-72" wire:ignore
             x-data="chart({
                type: 'line', labels: @js($labels),
                datasets: [{ label: 'Views', data: @js($series), __gradient: ['rgba(99,102,241,.28)','rgba(99,102,241,0)'],
                             borderColor: '#6366f1', fill: true, tension: .4, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 5 }]
             })">
            <canvas x-ref="canvas"></canvas>
        </div>
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Templates" :value="$stats['templates']" icon="layout" color="sky" :href="route('admin.templates')" />
        <x-ui.stat label="Media files" :value="number_format($stats['media'])" icon="image" color="rose" :href="route('admin.media')" />
        <x-ui.stat label="Blog posts" :value="$stats['posts']" icon="book" color="cyan" :href="route('admin.blog')" />
        <x-ui.stat label="New users" :value="$stats['newUsers']" icon="user-plus" color="emerald" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Recent signups" padding="p-0">
            <x-slot:actions><a href="{{ route('admin.users') }}" class="btn btn-ghost btn-sm">View all</a></x-slot:actions>
            @forelse($recentUsers as $user)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <x-ui.avatar :name="$user->name" :src="$user->avatar_url" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $user->email }}</p>
                    </div>
                    <span class="text-xs text-tertiary shrink-0">{{ $user->created_at->diffForHumans(short: true) }}</span>
                </div>
            @empty
                <x-ui.empty-state compact icon="users" title="No users yet" />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Recent websites" padding="p-0">
            <x-slot:actions><a href="{{ route('admin.websites') }}" class="btn btn-ghost btn-sm">View all</a></x-slot:actions>
            @forelse($recentWebsites as $site)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0">
                        <x-icon name="globe" class="w-4 h-4 text-brand-500" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $site->name }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $site->user?->name }}</p>
                    </div>
                    <x-ui.badge :color="$site->status->color()">{{ $site->status->label() }}</x-ui.badge>
                </div>
            @empty
                <x-ui.empty-state compact icon="globe" title="No websites yet" />
            @endforelse
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Open tickets" padding="p-0" class="lg:col-span-1">
            @forelse($tickets as $t)
                <a href="{{ route('admin.tickets') }}" class="flex items-start gap-3 px-5 py-3 border-b border-subtle last:border-0 hover:bg-surface-muted transition-colors">
                    <span @class(['w-2 h-2 rounded-full mt-1.5 shrink-0',
                                  'bg-rose-500' => $t->priority === 'urgent',
                                  'bg-amber-500' => $t->priority === 'high',
                                  'bg-sky-500' => !in_array($t->priority, ['urgent','high'])])></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $t->subject }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $t->user?->name }} · {{ $t->created_at->diffForHumans(short: true) }}</p>
                    </div>
                </a>
            @empty
                <x-ui.empty-state compact icon="check-circle" title="No open tickets" description="Inbox zero." />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Recent activity" padding="p-0" class="lg:col-span-2">
            <x-slot:actions><a href="{{ route('admin.activity') }}" class="btn btn-ghost btn-sm">View log</a></x-slot:actions>
            @forelse($activity as $log)
                <div class="flex items-start gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <span class="w-7 h-7 rounded-lg bg-surface-muted grid place-items-center shrink-0 mt-0.5">
                        <x-icon name="activity" class="w-3.5 h-3.5 text-tertiary" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm truncate">{{ $log->description }}</p>
                        <p class="text-xs text-tertiary">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans(short: true) }}</p>
                    </div>
                </div>
            @empty
                <x-ui.empty-state compact icon="activity" title="No activity recorded" />
            @endforelse
        </x-ui.card>
    </div>
</div>
