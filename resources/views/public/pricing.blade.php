@extends('layouts.public')
@section('meta_title', 'Pricing')
@section('meta_description', 'Simple, transparent pricing. Start free and upgrade when you are ready to launch.')

@section('content')
<div x-data="{ yearly: false }">

<section class="max-w-7xl mx-auto px-5 sm:px-8 pt-20 pb-12 text-center">
    <x-ui.badge color="brand">Pricing</x-ui.badge>
    <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight mt-5 leading-[1.05]">
        Pricing that scales<br>with your ambition
    </h1>
    <p class="text-lg text-secondary mt-6 max-w-xl mx-auto">
        Start free forever. Upgrade for custom domains, unlimited AI and premium templates.
    </p>

    <div class="inline-flex items-center gap-1 mt-9 p-1 rounded-xl bg-surface-muted border border-subtle">
        <button x-on:click="yearly = false" :class="!yearly ? 'bg-surface shadow-sm' : 'text-tertiary'" class="px-5 py-2 rounded-lg text-sm font-medium transition-all">Monthly</button>
        <button x-on:click="yearly = true" :class="yearly ? 'bg-surface shadow-sm' : 'text-tertiary'" class="px-5 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2">
            Yearly <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">Save 20%</span>
        </button>
    </div>
</section>

<section class="max-w-7xl mx-auto px-5 sm:px-8 pb-20">
    <div class="grid gap-5 lg:grid-cols-4 items-start">
        @foreach($plans as $plan)
            <div @class(['card p-6 relative flex flex-col h-full', 'ring-2 ring-brand-500 shadow-glow lg:scale-[1.03] lg:-my-2' => $plan->is_featured, 'card-hover' => !$plan->is_featured])>
                @if($plan->is_featured)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-r from-brand-500 to-violet-500 text-white text-[10px] font-bold uppercase tracking-wider shadow-lg shadow-brand-500/30">Most popular</span>
                @endif
                <h3 class="font-bold text-lg">{{ $plan->name }}</h3>
                <p class="text-xs text-tertiary mt-1 min-h-[2.5rem]">{{ $plan->description }}</p>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-4xl font-extrabold tracking-tight tabular-nums"
                          x-text="yearly ? '${{ number_format($plan->yearly_price / 12, 0) }}' : '${{ number_format($plan->monthly_price, 0) }}'"></span>
                    <span class="text-sm text-tertiary">/mo</span>
                </div>
                <p class="text-[11px] text-tertiary mt-1 h-4" x-show="yearly" x-cloak>${{ number_format($plan->yearly_price, 0) }} billed annually</p>
                <a href="{{ route('register') }}" @class(['btn w-full mt-5', 'btn-primary' => $plan->is_featured, 'btn-secondary' => !$plan->is_featured])>
                    {{ $plan->monthly_price > 0 ? 'Start free trial' : 'Get started free' }}
                </a>
                <ul class="space-y-2.5 mt-6 pt-5 border-t border-subtle flex-1">
                    @foreach($plan->features ?? [] as $feature)
                        <li class="flex items-start gap-2.5 text-sm">
                            <x-icon name="check" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                            <span class="text-secondary leading-snug">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>

{{-- comparison --}}
<section class="max-w-6xl mx-auto px-5 sm:px-8 py-16">
    <h2 class="text-3xl font-extrabold tracking-tight text-center mb-10">Compare every feature</h2>
    <div class="card table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="min-w-[220px]">Feature</th>
                    @foreach($plans as $plan)<th class="text-center">{{ $plan->name }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach([
                    ['Websites', 'max_websites'],
                    ['Pages per website', 'max_pages'],
                    ['Storage', 'max_storage_mb'],
                    ['AI credits / month', 'ai_credits'],
                    ['Custom domain', 'custom_domain'],
                    ['Remove branding', 'remove_branding'],
                    ['Premium templates', 'premium_templates'],
                    ['Priority support', 'priority_support'],
                ] as [$label, $key])
                    <tr>
                        <td class="font-medium">{{ $label }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @php $v = $plan->{$key}; @endphp
                                @if(is_bool($v))
                                    @if($v)<x-icon name="check-circle" class="w-4 h-4 text-emerald-500 inline" />
                                    @else<x-icon name="x" class="w-4 h-4 text-ink-300 dark:text-ink-600 inline" />@endif
                                @elseif($v < 0)
                                    <span class="font-semibold text-brand-600 dark:text-brand-400">Unlimited</span>
                                @elseif($key === 'max_storage_mb')
                                    <span class="tabular-nums">{{ $v >= 1024 ? round($v/1024, 1).' GB' : $v.' MB' }}</span>
                                @else
                                    <span class="tabular-nums">{{ number_format($v) }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

@if($faqs->isNotEmpty())
<section class="max-w-3xl mx-auto px-5 sm:px-8 py-16">
    <h2 class="text-3xl font-extrabold tracking-tight text-center mb-10">Billing questions</h2>
    <div class="space-y-3" x-data="{ open: null }">
        @foreach($faqs as $i => $faq)
            <div class="card overflow-hidden">
                <button x-on:click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-surface-muted transition-colors">
                    <span class="font-medium text-[15px]">{{ $faq->question }}</span>
                    <x-icon name="chevron-down" class="w-4 h-4 text-tertiary shrink-0 transition-transform" ::class="open === {{ $i }} ? 'rotate-180' : ''" />
                </button>
                <div x-show="open === {{ $i }}" x-collapse x-cloak><p class="px-5 pb-5 text-sm text-secondary leading-relaxed">{{ $faq->answer }}</p></div>
            </div>
        @endforeach
    </div>
</section>
@endif

<section class="max-w-7xl mx-auto px-5 sm:px-8 pb-20">
    <div class="rounded-3xl mesh-bg bg-ink-950 text-white p-12 sm:p-16 text-center">
        <h2 class="text-4xl font-extrabold tracking-tight">Ready to build?</h2>
        <p class="text-white/65 mt-4 text-lg">Start free — no credit card required.</p>
        <a href="{{ route('register') }}" class="btn btn-lg bg-white text-ink-950 hover:bg-white/90 mt-8 shadow-xl">
            Create your free account <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>
</section>
</div>
@endsection
