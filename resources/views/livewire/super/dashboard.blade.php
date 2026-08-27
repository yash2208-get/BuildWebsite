<div class="space-y-6">
    <x-ui.page-header title="Platform Overview" :description="'Everything happening across AuroraBuild · '.now()->format('l, F j, Y')">
        <div class="flex gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
            @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $d => $l)
                <button wire:click="$set('range', {{ $d }})"
                        @class(['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all',
                                'bg-surface shadow-sm' => $range === $d, 'text-tertiary hover:text-secondary' => $range !== $d])>{{ $l }}</button>
            @endforeach
        </div>
    </x-ui.page-header>

    {{-- ── headline revenue hero ── --}}
    <div class="relative overflow-hidden rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-brand-600 via-violet-600 to-fuchsia-600 text-white shadow-2xl shadow-brand-500/25">
        <div class="absolute -top-24 -right-16 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-12 w-72 h-72 rounded-full bg-cyan-300/20 blur-3xl"></div>

        <div class="relative grid gap-8 lg:grid-cols-[1.1fr_1fr] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70">Total revenue</p>
                <p class="text-4xl sm:text-5xl font-bold tracking-tight mt-2 tabular-nums">${{ number_format($stats['revenue'], 2) }}</p>
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur text-sm font-medium">
                        ${{ number_format($stats['periodRevenue'], 2) }} in {{ $range }}d
                    </span>
                    @if($stats['revenueDelta'] !== null)
                        <span @class(['inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold backdrop-blur',
                                      'bg-emerald-400/25' => $stats['revenueDelta'] >= 0, 'bg-rose-400/25' => $stats['revenueDelta'] < 0])>
                            <x-icon :name="$stats['revenueDelta'] >= 0 ? 'trend-up' : 'trend-down'" class="w-3.5 h-3.5" />
                            {{ abs($stats['revenueDelta']) }}%
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:gap-5">
                @foreach([
                    ['MRR', '$'.number_format($stats['mrr'], 2)],
                    ['ARR', '$'.number_format($stats['arr'], 2)],
                    ['ARPU', '$'.number_format($stats['arpu'], 2)],
                    ['Conversion', $stats['conversion'].'%'],
                ] as [$l, $v])
                    <div class="rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15 px-4 py-3.5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-white/65">{{ $l }}</p>
                        <p class="text-xl font-bold tabular-nums mt-1">{{ $v }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── primary KPIs ── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Customers" :value="number_format($stats['users'])" icon="users" color="brand" :href="route('super.users')" />
        <x-ui.stat label="Active subscriptions" :value="number_format($stats['subscriptions'])" icon="credit-card" color="emerald" :href="route('super.subscriptions')" />
        <x-ui.stat label="Websites" :value="number_format($stats['websites'])" icon="globe" color="violet" :href="route('super.websites')" />
        <x-ui.stat label="Total page views" :value="number_format($stats['views'])" icon="eye" color="sky" />
    </div>

    {{-- ── revenue + traffic charts ── --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Revenue" :subtitle="'Paid invoices over the last '.$range.' days'" class="lg:col-span-2">
            <div class="h-72" wire:ignore
                 x-data="chart({
                    type: 'line', labels: @js($labels),
                    datasets: [{ label: 'Revenue', data: @js($revenueSeries), __gradient: ['rgba(16,185,129,.3)','rgba(16,185,129,0)'],
                                 borderColor: '#10b981', fill: true, tension: .4, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 5 }],
                    money: true
                 })">
                <canvas x-ref="canvas"></canvas>
            </div>
        </x-ui.card>

        <x-ui.card title="Plan mix" subtitle="Active subscriptions by plan">
            @if($planBreakdown->isNotEmpty())
                <div class="h-44" wire:ignore
                     x-data="chart({
                        type: 'doughnut', labels: @js($planBreakdown->pluck('name')),
                        datasets: [{ data: @js($planBreakdown->pluck('total')),
                                     backgroundColor: ['#6366f1','#8b5cf6','#22d3ee','#10b981','#f59e0b'],
                                     borderWidth: 0, hoverOffset: 8 }],
                        cutout: '68%', legend: false
                     })">
                    <canvas x-ref="canvas"></canvas>
                </div>
                <div class="space-y-2.5 mt-5">
                    @foreach($planBreakdown as $i => $p)
                        <div class="flex items-center gap-2.5 text-sm">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ ['#6366f1','#8b5cf6','#22d3ee','#10b981','#f59e0b'][$i % 5] }}"></span>
                            <span class="flex-1 truncate">{{ $p->name }}</span>
                            <span class="text-tertiary tabular-nums">{{ $p->total }}</span>
                            <span class="font-semibold tabular-nums">${{ number_format((float) $p->revenue, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state compact icon="credit-card" title="No active subscriptions" />
            @endif
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Traffic" :subtitle="number_format(array_sum($trafficSeries)).' page views'">
            <div class="h-56" wire:ignore
                 x-data="chart({
                    type: 'bar', labels: @js($labels),
                    datasets: [{ label: 'Views', data: @js($trafficSeries), backgroundColor: 'rgba(99,102,241,.75)', borderRadius: 5, maxBarThickness: 22 }]
                 })">
                <canvas x-ref="canvas"></canvas>
            </div>
        </x-ui.card>

        <x-ui.card title="New signups" :subtitle="$stats['newUsers'].' new customers'">
            <div class="h-56" wire:ignore
                 x-data="chart({
                    type: 'line', labels: @js($labels),
                    datasets: [{ label: 'Signups', data: @js($signupSeries), __gradient: ['rgba(139,92,246,.28)','rgba(139,92,246,0)'],
                                 borderColor: '#8b5cf6', fill: true, tension: .4, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 5 }]
                 })">
                <canvas x-ref="canvas"></canvas>
            </div>
        </x-ui.card>
    </div>

    {{-- ── secondary KPIs ── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="AI generations" :value="number_format($stats['aiGenerations'])" icon="sparkles" color="violet" :href="route('super.ai-logs')" />
        <x-ui.stat label="AI credits used" :value="number_format($stats['aiCredits'])" icon="zap" color="amber" />
        <x-ui.stat label="Open tickets" :value="$stats['openTickets']" icon="ticket" color="rose" :href="route('super.tickets')" />
        <x-ui.stat label="Unread messages" :value="$stats['unreadContacts']" icon="mail" color="sky" :href="route('super.contacts')" />
    </div>

    {{-- ── lists ── --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Recent payments" padding="p-0">
            <x-slot:actions><a href="{{ route('super.invoices') }}" class="btn btn-ghost btn-sm">All invoices</a></x-slot:actions>
            @forelse($recentInvoices as $inv)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <x-ui.avatar :name="$inv->user?->name ?? '—'" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $inv->user?->name }}</p>
                        <p class="text-xs text-tertiary font-mono truncate">{{ $inv->invoice_number }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold tabular-nums">${{ number_format((float) $inv->total, 2) }}</p>
                        <p class="text-xs text-tertiary">{{ $inv->paid_at?->diffForHumans(short: true) }}</p>
                    </div>
                </div>
            @empty
                <x-ui.empty-state compact icon="receipt" title="No payments yet" />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Top websites" padding="p-0" subtitle="By lifetime page views">
            @forelse($topWebsites as $site)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0">
                        <x-icon name="globe" class="w-4 h-4 text-brand-500" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $site->name }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $site->user?->name }}</p>
                    </div>
                    <span class="text-sm font-semibold tabular-nums shrink-0">{{ number_format($site->views_count) }}</span>
                </div>
            @empty
                <x-ui.empty-state compact icon="globe" title="No websites yet" />
            @endforelse
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Newest customers" padding="p-0">
            @forelse($recentUsers as $u)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <x-ui.avatar :name="$u->name" :src="$u->avatar_url" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $u->name }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $u->email }}</p>
                    </div>
                </div>
            @empty
                <x-ui.empty-state compact icon="users" title="No customers" />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Security events" padding="p-0">
            <x-slot:actions><a href="{{ route('super.security') }}" class="btn btn-ghost btn-sm">View all</a></x-slot:actions>
            @forelse($security as $s)
                <div class="flex items-start gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <span @class(['w-2 h-2 rounded-full mt-1.5 shrink-0',
                                  'bg-rose-500' => $s->level === 'critical', 'bg-amber-500' => $s->level === 'warning',
                                  'bg-emerald-500' => $s->level === 'success', 'bg-slate-400' => !in_array($s->level, ['critical','warning','success'])])></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm truncate">{{ Str::headline($s->event) }}</p>
                        <p class="text-xs text-tertiary truncate font-mono">{{ $s->ip_address }} · {{ $s->created_at->diffForHumans(short: true) }}</p>
                    </div>
                </div>
            @empty
                <x-ui.empty-state compact icon="shield" title="All clear" />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Activity" padding="p-0">
            <x-slot:actions><a href="{{ route('super.activity') }}" class="btn btn-ghost btn-sm">Audit log</a></x-slot:actions>
            @forelse($activity as $log)
                <div class="px-5 py-3 border-b border-subtle last:border-0">
                    <p class="text-sm truncate">{{ $log->description }}</p>
                    <p class="text-xs text-tertiary truncate">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans(short: true) }}</p>
                </div>
            @empty
                <x-ui.empty-state compact icon="activity" title="No activity" />
            @endforelse
        </x-ui.card>
    </div>
</div>
