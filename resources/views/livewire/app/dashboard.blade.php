<div class="space-y-7">

    {{-- ===== Hero greeting ===== --}}
    <div class="relative overflow-hidden rounded-3xl mesh-bg surface p-7 sm:p-9">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-6 justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-500 mb-2">
                    {{ now()->format('l, j F') }}
                </p>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Welcome back, <span class="text-gradient">{{ str($this->user()->name)->before(' ') }}</span>
                </h1>
                <p class="text-secondary mt-2 max-w-xl">
                    You have {{ $stats['total'] }} {{ Str::plural('website', $stats['total']) }},
                    {{ $stats['published'] }} live and reaching
                    <span class="font-semibold text-brand-600 dark:text-brand-300">{{ number_format($views) }}</span> views this period.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('app.ai') }}" class="btn btn-primary">
                    <x-icon name="sparkles" class="w-4 h-4" /> Generate with AI
                </a>
                <a href="{{ route('app.websites') }}" class="btn btn-secondary">
                    <x-icon name="plus" class="w-4 h-4" /> New website
                </a>
            </div>
        </div>
    </div>

    {{-- ===== Stat cards ===== --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4 stagger">
        <x-ui.stat label="Total websites" :value="number_format($stats['total'])" icon="globe" color="brand"
                   :href="route('app.websites')" />
        <x-ui.stat label="Published" :value="number_format($stats['published'])" icon="rocket" color="emerald" />
        <x-ui.stat label="Views ({{ $range }}d)" :value="number_format($views)" icon="chart" color="sky"
                   :change="abs($delta).'%'" :trend="$delta >= 0 ? 'up' : 'down'" />
        <x-ui.stat label="AI generations" :value="number_format($aiCount)" icon="sparkles" color="violet"
                   :href="route('app.ai')" />
    </div>

    {{-- ===== Chart + usage ===== --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 card">
            <div class="flex items-center justify-between gap-4 px-6 pt-5 pb-4 border-b border-subtle">
                <div>
                    <h3 class="font-semibold tracking-tight">Traffic overview</h3>
                    <p class="text-sm text-tertiary mt-0.5">{{ number_format($visitors) }} unique visitors</p>
                </div>
                <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
                    @foreach(['7' => '7D', '30' => '30D', '60' => '60D'] as $val => $label)
                        <button wire:click="$set('range', '{{ $val }}')"
                                @class([
                                    'px-3 py-1 text-xs font-semibold rounded-lg transition-all',
                                    'bg-surface shadow-sm text-brand-600 dark:text-brand-300' => $range === $val,
                                    'text-tertiary hover:text-secondary' => $range !== $val,
                                ])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="p-6">
                @if($chartViews->sum() > 0)
                    <div wire:ignore class="h-[280px]" x-data="chart({
                        type: 'line',
                        data: {
                            labels: @js($chartLabels),
                            datasets: [
                                {
                                    label: 'Views', data: @js($chartViews),
                                    borderColor: '#6366f1', borderWidth: 2.5, tension: 0.4,
                                    pointRadius: 0, pointHoverRadius: 5, pointHoverBackgroundColor: '#6366f1',
                                    fill: true, __gradient: ['rgba(99,102,241,.28)', 'rgba(99,102,241,0)'],
                                },
                                {
                                    label: 'Visitors', data: @js($chartVisitors),
                                    borderColor: '#22d3ee', borderWidth: 2, tension: 0.4,
                                    pointRadius: 0, pointHoverRadius: 5, borderDash: [5, 4], fill: false,
                                },
                            ],
                        },
                        options: { interaction: { intersect: false, mode: 'index' } },
                    })">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                @else
                    <x-ui.empty-state icon="chart" title="No traffic yet"
                                      description="Publish a website and analytics will start flowing in here." />
                @endif
            </div>
        </div>

        <x-ui.card title="Plan usage" subtitle="{{ $this->user()->activePlan()?->name ?? 'Free' }} plan">
            <div class="space-y-5">
                @php $q = app(\App\Services\Website\QuotaService::class); @endphp
                <div>
                    <x-ui.progress :value="$usage['websites']['used']" :max="max(1, $usage['websites']['limit'])" label="Websites" />
                    <p class="text-xs text-tertiary mt-1.5">
                        {{ $usage['websites']['used'] }} of {{ $usage['websites']['limit'] < 0 ? 'unlimited' : $usage['websites']['limit'] }} used
                    </p>
                </div>
                <div>
                    <x-ui.progress :value="$usage['ai_credits']['used']" :max="max(1, $usage['ai_credits']['limit'])" label="AI credits" color="sky" />
                    <p class="text-xs text-tertiary mt-1.5">
                        {{ $usage['ai_credits']['used'] }} of {{ $usage['ai_credits']['limit'] < 0 ? 'unlimited' : $usage['ai_credits']['limit'] }} this month
                    </p>
                </div>
                <div>
                    <x-ui.progress :value="(int) $usage['storage']['used']" :max="max(1, $usage['storage']['limit'])" label="Storage" color="emerald" />
                    <p class="text-xs text-tertiary mt-1.5">
                        {{ $usage['storage']['used'] }} MB of {{ $usage['storage']['limit'] < 0 ? 'unlimited' : $usage['storage']['limit'].' MB' }}
                    </p>
                </div>
                <a href="{{ route('app.billing') }}" class="btn btn-secondary w-full">
                    <x-icon name="zap" class="w-4 h-4" /> Manage plan
                </a>
            </div>
        </x-ui.card>
    </div>

    {{-- ===== Recent websites + AI ===== --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card padding="p-0" title="Recent websites" subtitle="Jump back into what you were building">
                <x-slot:actions>
                    <a href="{{ route('app.websites') }}" class="btn btn-ghost btn-sm">
                        View all <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </x-slot:actions>

                @forelse($recent as $site)
                    <div class="flex items-center gap-4 px-6 py-4 border-b border-subtle last:border-0 hover:bg-surface-muted transition-colors group">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0 ring-1 ring-brand-500/12">
                            <x-icon name="globe" class="w-5 h-5 text-brand-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm truncate">{{ $site->name }}</p>
                                <x-ui.badge :color="$site->status->color()" dot>{{ $site->status->label() }}</x-ui.badge>
                            </div>
                            <p class="text-xs text-tertiary truncate mt-0.5">
                                {{ $site->display_domain }} · {{ $site->pages_count }} {{ Str::plural('page', $site->pages_count) }}
                                · {{ number_format($site->views_count) }} views
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('builder.edit', $site) }}" class="btn btn-secondary btn-sm">
                                <x-icon name="edit" class="w-3.5 h-3.5" /> Edit
                            </a>
                            <a href="{{ route('site.preview', $site->subdomain) }}" target="_blank" class="btn btn-ghost btn-icon" title="Preview">
                                <x-icon name="external" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state icon="globe" title="No websites yet"
                                      description="Create your first website — or let the AI build a complete one for you in seconds.">
                        <a href="{{ route('app.ai') }}" class="btn btn-primary"><x-icon name="sparkles" class="w-4 h-4" /> Generate with AI</a>
                        <a href="{{ route('app.templates') }}" class="btn btn-secondary">Browse templates</a>
                    </x-ui.empty-state>
                @endforelse
            </x-ui.card>
        </div>

        <x-ui.card padding="p-0" title="AI activity" subtitle="Your latest generations">
            @forelse($aiRecent as $gen)
                <div class="flex items-start gap-3 px-5 py-3.5 border-b border-subtle last:border-0">
                    <span class="w-8 h-8 rounded-lg bg-violet-500/12 text-violet-500 grid place-items-center shrink-0">
                        <x-icon :name="$gen->type->icon()" class="w-4 h-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $gen->type->label() }}</p>
                        <p class="text-xs text-tertiary truncate">{{ Str::limit($gen->prompt, 46) }}</p>
                        <p class="text-[11px] text-tertiary mt-0.5">{{ $gen->created_at->diffForHumans() }}</p>
                    </div>
                    @if($gen->status === 'failed')
                        <x-ui.badge color="rose">Failed</x-ui.badge>
                    @endif
                </div>
            @empty
                <x-ui.empty-state compact icon="sparkles" title="No AI activity"
                                  description="Try the AI Studio to generate a site.">
                    <a href="{{ route('app.ai') }}" class="btn btn-primary btn-sm">Open AI Studio</a>
                </x-ui.empty-state>
            @endforelse
        </x-ui.card>
    </div>
</div>
