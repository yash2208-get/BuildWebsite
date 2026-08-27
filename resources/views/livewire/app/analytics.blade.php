<div class="space-y-6">
    <x-ui.page-header title="Analytics" description="Traffic and engagement across your websites.">
        <select wire:model.live="websiteId" class="field w-auto">
            <option value="all">All websites</option>
            @foreach($websites as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
        </select>
        <div class="flex gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
            @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $days => $label)
                <button wire:click="$set('range', {{ $days }})"
                        @class(['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all',
                                'bg-surface shadow-sm' => $range === $days, 'text-tertiary hover:text-secondary' => $range !== $days])>{{ $label }}</button>
            @endforeach
        </div>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total views" :value="number_format($totalViews)" icon="eye" color="brand" :trend="$delta" />
        <x-ui.stat label="Unique visitors" :value="number_format($totalVisitors)" icon="users" color="violet" />
        <x-ui.stat label="Avg. daily views" :value="number_format($avgDaily)" icon="chart" color="cyan" />
        <x-ui.stat label="Bounce rate" :value="$bounceRate.'%'" icon="activity" color="amber" />
    </div>

    <x-ui.card title="Traffic over time" :subtitle="'Last '.$range.' days'">
        <div class="h-72" wire:ignore
             x-data="chart({
                type: 'line',
                labels: @js($labels),
                datasets: [
                    { label: 'Views', data: @js($views), __gradient: ['rgba(99,102,241,.28)','rgba(99,102,241,0)'], borderColor: '#6366f1', fill: true, tension: .4, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 5 },
                    { label: 'Visitors', data: @js($visitors), borderColor: '#22d3ee', fill: false, tension: .4, borderWidth: 2, borderDash: [5,4], pointRadius: 0, pointHoverRadius: 5 }
                ]
             })">
            <canvas x-ref="canvas"></canvas>
        </div>
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Top pages" padding="p-0">
            @forelse($topPages as $page)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <span class="w-7 h-7 rounded-lg bg-brand-500/10 text-brand-500 grid place-items-center text-[11px] font-bold shrink-0">
                        {{ $loop->iteration }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $page->title }}</p>
                        <p class="text-xs text-tertiary truncate">/{{ $page->slug }}</p>
                    </div>
                    <span class="text-sm font-semibold tabular-nums shrink-0">{{ number_format($page->views_count) }}</span>
                </div>
            @empty
                <x-ui.empty-state compact icon="file" title="No page data yet" description="Publish a site to start collecting analytics." />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Top websites" padding="p-0">
            @forelse($topSites as $site)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0">
                        <x-icon name="globe" class="w-4 h-4 text-brand-500" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $site->name }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $site->subdomain }}</p>
                    </div>
                    <span class="text-sm font-semibold tabular-nums shrink-0">{{ number_format($site->views_count) }}</span>
                </div>
            @empty
                <x-ui.empty-state compact icon="globe" title="No websites yet" />
            @endforelse
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Devices">
            <div class="grid sm:grid-cols-2 gap-6 items-center">
                <div class="h-48" wire:ignore
                     x-data="chart({
                        type: 'doughnut',
                        labels: @js(collect($devices)->pluck('label')),
                        datasets: [{ data: @js(collect($devices)->pluck('value')), backgroundColor: @js(collect($devices)->pluck('color')), borderWidth: 0, cutout: '68%' }]
                     })">
                    <canvas x-ref="canvas"></canvas>
                </div>
                <div class="space-y-3">
                    @foreach($devices as $device)
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $device['color'] }}"></span>
                            <x-icon :name="$device['icon']" class="w-4 h-4 text-tertiary" />
                            <span class="text-sm flex-1">{{ $device['label'] }}</span>
                            <span class="text-sm font-semibold tabular-nums">{{ $device['value'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Traffic sources">
            <div class="space-y-4">
                @foreach($sources as $source)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm">{{ $source['label'] }}</span>
                            <span class="text-sm font-semibold tabular-nums">{{ $source['value'] }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-surface-muted overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-cyan-400 transition-all duration-700"
                                 style="width: {{ $source['value'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</div>
