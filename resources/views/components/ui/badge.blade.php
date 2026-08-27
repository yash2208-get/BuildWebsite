@props(['color' => 'slate', 'dot' => false])
@php
    $map = [
        'brand'   => 'bg-brand-500/12 text-brand-600 dark:text-brand-300 ring-1 ring-brand-500/20',
        'indigo'  => 'bg-indigo-500/12 text-indigo-600 dark:text-indigo-300 ring-1 ring-indigo-500/20',
        'emerald' => 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-300 ring-1 ring-emerald-500/20',
        'amber'   => 'bg-amber-500/14 text-amber-600 dark:text-amber-300 ring-1 ring-amber-500/25',
        'rose'    => 'bg-rose-500/12 text-rose-600 dark:text-rose-300 ring-1 ring-rose-500/20',
        'sky'     => 'bg-sky-500/12 text-sky-600 dark:text-sky-300 ring-1 ring-sky-500/20',
        'violet'  => 'bg-violet-500/12 text-violet-600 dark:text-violet-300 ring-1 ring-violet-500/20',
        'fuchsia' => 'bg-fuchsia-500/12 text-fuchsia-600 dark:text-fuchsia-300 ring-1 ring-fuchsia-500/20',
        'slate'   => 'bg-slate-500/12 text-slate-600 dark:text-slate-300 ring-1 ring-slate-500/20',
    ];
    $dots = [
        'brand' => 'bg-brand-500', 'indigo' => 'bg-indigo-500', 'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'sky' => 'bg-sky-500',
        'violet' => 'bg-violet-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-400',
    ];
@endphp
<span {{ $attributes->class(['badge', $map[$color] ?? $map['slate']]) }}>
    @if($dot)<span class="w-1.5 h-1.5 rounded-full {{ $dots[$color] ?? $dots['slate'] }}"></span>@endif
    {{ $slot }}
</span>
