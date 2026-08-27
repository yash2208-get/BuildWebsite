@props(['value' => 0, 'max' => 100, 'color' => 'brand', 'label' => null, 'showValue' => true])
@php
    $pct = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;
    $tone = $pct >= 90 ? 'from-rose-500 to-red-500' : ($pct >= 70 ? 'from-amber-500 to-orange-500' : match($color){
        'emerald' => 'from-emerald-500 to-teal-500',
        'sky' => 'from-sky-500 to-cyan-500',
        default => 'from-brand-500 to-violet-500',
    });
@endphp
<div {{ $attributes }}>
    @if($label || $showValue)
        <div class="flex items-center justify-between text-xs mb-1.5">
            @if($label)<span class="text-secondary font-medium">{{ $label }}</span>@endif
            @if($showValue)<span class="text-tertiary tabular-nums">{{ $pct }}%</span>@endif
        </div>
    @endif
    <div class="h-2 rounded-full bg-surface-muted overflow-hidden ring-1 ring-inset ring-black/5 dark:ring-white/5">
        <div class="h-full rounded-full bg-gradient-to-r {{ $tone }} transition-all duration-700 ease-out" style="width: {{ $pct }}%"></div>
    </div>
</div>
