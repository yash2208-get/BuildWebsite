@props(['label', 'value', 'icon' => null, 'change' => null, 'trend' => null, 'color' => 'brand', 'href' => null])
@php
    $tone = [
        'brand' => 'from-brand-500 to-violet-500', 'emerald' => 'from-emerald-500 to-teal-500',
        'amber' => 'from-amber-500 to-orange-500', 'rose' => 'from-rose-500 to-pink-500',
        'sky' => 'from-sky-500 to-cyan-500', 'violet' => 'from-violet-500 to-fuchsia-500',
    ][$color] ?? 'from-brand-500 to-violet-500';
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class(['card card-hover p-5 block group']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wider text-tertiary">{{ $label }}</p>
            <p class="text-2xl font-bold tracking-tight mt-2 tabular-nums">{{ $value }}</p>
        </div>
        @if($icon)
            <span class="shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br {{ $tone }} grid place-items-center text-white shadow-lg shadow-brand-500/20 group-hover:scale-105 transition-transform">
                <x-icon :name="$icon" class="w-5 h-5" />
            </span>
        @endif
    </div>
    @if($change !== null)
        <div class="flex items-center gap-1.5 mt-3 text-xs font-medium">
            <span @class([
                'inline-flex items-center gap-0.5',
                'text-emerald-600 dark:text-emerald-400' => $trend === 'up',
                'text-rose-600 dark:text-rose-400' => $trend === 'down',
                'text-tertiary' => ! in_array($trend, ['up','down']),
            ])>
                @if($trend === 'up')<x-icon name="arrow-up" class="w-3.5 h-3.5" />
                @elseif($trend === 'down')<x-icon name="arrow-down" class="w-3.5 h-3.5" />@endif
                {{ $change }}
            </span>
            <span class="text-tertiary">vs last period</span>
        </div>
    @endif
</{{ $tag }}>
