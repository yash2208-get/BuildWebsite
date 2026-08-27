@extends('layouts.public')

@section('meta_title', 'Build stunning websites with AI')
@section('meta_description', 'Describe your idea and get a complete, responsive website in seconds. Drag-and-drop editing, custom domains and one-click publishing.')

@section('content')

{{-- ══════════════ HERO ══════════════ --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-[-18rem] left-1/2 -translate-x-1/2 w-[75rem] h-[45rem] rounded-full blur-[130px] opacity-30
                    bg-[radial-gradient(circle_at_30%_40%,#6366f1,transparent_60%),radial-gradient(circle_at_70%_60%,#22d3ee,transparent_55%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgb(var(--border-subtle)/.5)_1px,transparent_1px),linear-gradient(to_bottom,rgb(var(--border-subtle)/.5)_1px,transparent_1px)]
                    bg-[size:56px_56px] [mask-image:radial-gradient(ellipse_70%_55%_at_50%_0%,#000,transparent)]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-5 sm:px-8 pt-20 pb-24 lg:pt-28 lg:pb-32 text-center">
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full surface text-xs font-semibold mb-8 hover:border-brand-500/40 transition-colors group animate-[fade-up_.5s_cubic-bezier(.16,1,.3,1)_both]">
            <span class="flex items-center gap-1.5 text-brand-600 dark:text-brand-400">
                <x-icon name="sparkles" class="w-3.5 h-3.5" /> New
            </span>
            <span class="w-px h-3 bg-[rgb(var(--border-subtle))]"></span>
            <span class="text-secondary">AI generates your entire site in one prompt</span>
            <x-icon name="arrow-right" class="w-3 h-3 text-tertiary group-hover:translate-x-0.5 transition-transform" />
        </a>

        <h1 class="text-[clamp(2.5rem,7vw,5rem)] font-extrabold tracking-[-0.03em] leading-[1.03] max-w-5xl mx-auto animate-[fade-up_.55s_cubic-bezier(.16,1,.3,1)_.05s_both]">
            The AI website builder<br class="hidden sm:block">
            <span class="bg-gradient-to-r from-brand-500 via-violet-500 to-cyan-400 bg-clip-text text-transparent">for modern teams</span>
        </h1>

        <p class="text-lg sm:text-xl text-secondary mt-7 max-w-2xl mx-auto leading-relaxed animate-[fade-up_.55s_cubic-bezier(.16,1,.3,1)_.12s_both]">
            Describe your idea in a sentence. Get a complete, responsive, production-ready website —
            then refine every pixel in a Webflow-class visual editor.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-10 animate-[fade-up_.55s_cubic-bezier(.16,1,.3,1)_.2s_both]">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg w-full sm:w-auto">
                Start building free <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
            <a href="{{ route('templates.public') }}" class="btn btn-secondary btn-lg w-full sm:w-auto">
                <x-icon name="grid" class="w-4 h-4" /> Browse templates
            </a>
        </div>

        <p class="text-xs text-tertiary mt-5 flex items-center justify-center gap-4 flex-wrap animate-[fade-up_.55s_cubic-bezier(.16,1,.3,1)_.26s_both]">
            @foreach(['No credit card required', 'Free forever plan', 'Publish in minutes'] as $perk)
                <span class="flex items-center gap-1.5"><x-icon name="check-circle" class="w-3.5 h-3.5 text-emerald-500" /> {{ $perk }}</span>
            @endforeach
        </p>

        {{-- product shot --}}
        <div class="mt-16 lg:mt-20 relative animate-[fade-up_.7s_cubic-bezier(.16,1,.3,1)_.34s_both]">
            <div class="absolute -inset-x-8 -inset-y-4 bg-gradient-to-r from-brand-500/16 via-violet-500/12 to-cyan-400/16 blur-3xl -z-10 rounded-[3rem]"></div>
            <div class="card shadow-2xl overflow-hidden max-w-5xl mx-auto ring-1 ring-black/5 dark:ring-white/10">
                {{-- browser chrome --}}
                <div class="flex items-center gap-2 px-4 py-3 border-b border-subtle bg-surface-muted">
                    <div class="flex gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                    </div>
                    <div class="flex-1 flex justify-center">
                        <div class="px-4 py-1 rounded-md bg-surface text-[11px] text-tertiary font-mono flex items-center gap-1.5">
                            <x-icon name="lock" class="w-2.5 h-2.5" /> {{ config('platform.subdomain_host') }}/studio
                        </div>
                    </div>
                    <div class="flex gap-1"><span class="w-4 h-3 rounded-sm bg-[rgb(var(--border-subtle))]"></span></div>
                </div>

                {{-- editor mock --}}
                <div class="grid grid-cols-12 h-[380px] sm:h-[440px] bg-surface">
                    <div class="col-span-3 sm:col-span-2 border-r border-subtle p-3 space-y-2 bg-surface-muted/40">
                        @foreach(['Hero', 'Features', 'Pricing', 'FAQ', 'Footer'] as $i => $block)
                            <div @class(['rounded-lg p-2.5 text-[10px] font-medium flex items-center gap-1.5 transition-colors',
                                'bg-brand-500/12 text-brand-600 dark:text-brand-300 ring-1 ring-brand-500/20' => $i === 0,
                                'text-tertiary hover:bg-surface-muted' => $i !== 0])>
                                <span class="w-4 h-4 rounded bg-current opacity-20"></span>
                                <span class="hidden sm:inline">{{ $block }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-span-9 sm:col-span-8 p-6 sm:p-8 flex flex-col justify-center bg-gradient-to-br from-brand-500/[.05] to-cyan-400/[.04]">
                        <div class="h-2.5 w-20 rounded-full bg-brand-500/40 mb-4"></div>
                        <div class="h-7 sm:h-9 w-4/5 rounded-lg bg-[rgb(var(--text-primary))] opacity-[.14] mb-3"></div>
                        <div class="h-7 sm:h-9 w-3/5 rounded-lg bg-gradient-to-r from-brand-500/45 to-cyan-400/35 mb-5"></div>
                        <div class="space-y-2 mb-6">
                            <div class="h-2.5 w-full rounded-full bg-[rgb(var(--text-primary))] opacity-[.08]"></div>
                            <div class="h-2.5 w-4/5 rounded-full bg-[rgb(var(--text-primary))] opacity-[.08]"></div>
                        </div>
                        <div class="flex gap-2 mb-7">
                            <div class="h-9 w-28 rounded-lg bg-gradient-to-r from-brand-500 to-violet-500 shadow-lg shadow-brand-500/25"></div>
                            <div class="h-9 w-24 rounded-lg border border-subtle"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([0,1,2] as $c)
                                <div class="rounded-xl border border-subtle p-3 bg-surface/60 backdrop-blur">
                                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-brand-500/50 to-cyan-400/40 mb-2"></div>
                                    <div class="h-2 w-full rounded-full bg-[rgb(var(--text-primary))] opacity-[.1] mb-1.5"></div>
                                    <div class="h-2 w-2/3 rounded-full bg-[rgb(var(--text-primary))] opacity-[.07]"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="hidden sm:block col-span-2 border-l border-subtle p-3 space-y-3 bg-surface-muted/40">
                        @foreach(['Layout', 'Spacing', 'Typography', 'Colors'] as $panel)
                            <div>
                                <div class="text-[9px] font-bold uppercase tracking-wider text-tertiary mb-1.5">{{ $panel }}</div>
                                <div class="h-6 rounded-md bg-surface border border-subtle"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════ LOGOS ══════════════ --}}
<section class="max-w-7xl mx-auto px-5 sm:px-8 py-14 border-y border-subtle">
    <p class="text-center text-xs font-semibold uppercase tracking-[.14em] text-tertiary mb-8">
        Powering 48,000+ websites for teams worldwide
    </p>
    <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 opacity-45">
        @foreach(['Northbeam', 'Lumen', 'Kestrel', 'Vantage', 'Orbital', 'Fathom'] as $logo)
            <span class="text-lg font-bold tracking-tight">{{ $logo }}</span>
        @endforeach
    </div>
</section>

{{-- ══════════════ FEATURES ══════════════ --}}
<section class="max-w-7xl mx-auto px-5 sm:px-8 py-24">
    <div class="max-w-2xl mx-auto text-center mb-16">
        <x-ui.badge color="brand"><x-icon name="zap" class="w-3.5 h-3.5" /> Everything included</x-ui.badge>
        <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mt-5 leading-[1.1]">
            A complete studio,<br>not just a page builder
        </h2>
        <p class="text-lg text-secondary mt-5 leading-relaxed">
            From the first prompt to a live custom domain — every tool you need in one place.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['sparkles', 'AI Website Generator', 'Describe your business and get a full multi-page site — copy, structure, colours and layout included.', 'brand'],
            ['cursor', 'Visual Drag & Drop', 'A true canvas editor with layers, style manager and responsive breakpoints. No code required.', 'violet'],
            ['devices', 'Responsive by Default', 'Design once, then fine-tune desktop, laptop, tablet and mobile independently.', 'cyan'],
            ['grid', 'Component Library', 'Heroes, pricing tables, testimonials, FAQs, galleries, forms — 30+ production-ready blocks.', 'emerald'],
            ['palette', 'Theme Builder', 'Craft colour systems and typography scales that cascade across every page instantly.', 'amber'],
            ['search', 'SEO Manager', 'Meta tags, Open Graph, sitemaps, structured data and live search-result previews.', 'sky'],
            ['globe', 'Custom Domains', 'Connect your own domain with automatic SSL, or launch instantly on a free subdomain.', 'rose'],
            ['chart', 'Built-in Analytics', 'Track views, visitors, top pages and traffic sources without a third-party script.', 'indigo'],
            ['code', 'Custom Code & Export', 'Inject custom CSS/JS, or export clean, dependency-free HTML you fully own.', 'slate'],
        ] as [$icon, $title, $desc, $color])
            <div class="card card-hover p-6 group">
                <span class="w-11 h-11 rounded-xl grid place-items-center mb-4 transition-transform group-hover:scale-105 bg-{{ $color }}-500/12 text-{{ $color }}-500">
                    <x-icon :name="$icon" class="w-[22px] h-[22px]" />
                </span>
                <h3 class="font-semibold text-[15px]">{{ $title }}</h3>
                <p class="text-sm text-secondary mt-2 leading-relaxed">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ══════════════ HOW IT WORKS ══════════════ --}}
<section class="relative overflow-hidden py-24">
    <div class="absolute inset-0 -z-10 bg-surface-muted"></div>
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="max-w-2xl mx-auto text-center mb-16">
            <x-ui.badge color="violet">How it works</x-ui.badge>
            <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mt-5 leading-[1.1]">Live in three steps</h2>
        </div>

        <div class="grid gap-8 lg:grid-cols-3 relative">
            <div class="hidden lg:block absolute top-12 left-[16%] right-[16%] h-px bg-gradient-to-r from-transparent via-brand-500/25 to-transparent"></div>

            @foreach([
                ['1', 'Describe your idea', 'Tell the AI what you are building — your industry, audience and tone of voice.', 'pencil'],
                ['2', 'Refine visually', 'Drag, drop and restyle anything on the canvas. Swap blocks, adjust breakpoints, add pages.', 'cursor'],
                ['3', 'Publish anywhere', 'Go live on a free subdomain or connect your own domain with automatic SSL.', 'rocket'],
            ] as [$num, $title, $desc, $icon])
                <div class="relative text-center">
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-surface border border-subtle shadow-lg grid place-items-center relative z-10">
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-violet-500 text-white text-xs font-bold grid place-items-center shadow-lg shadow-brand-500/30">{{ $num }}</span>
                        <x-icon :name="$icon" class="w-9 h-9 text-brand-500" />
                    </div>
                    <h3 class="font-semibold text-lg mt-6">{{ $title }}</h3>
                    <p class="text-sm text-secondary mt-2.5 leading-relaxed max-w-xs mx-auto">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════ TEMPLATES ══════════════ --}}
@if($templates->isNotEmpty())
<section class="max-w-7xl mx-auto px-5 sm:px-8 py-24">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-5 mb-12">
        <div>
            <x-ui.badge color="emerald">Templates</x-ui.badge>
            <h2 class="text-4xl font-extrabold tracking-tight mt-4 leading-tight">Start from a beautiful base</h2>
            <p class="text-secondary mt-3 max-w-lg">Every template is fully editable and responsive out of the box.</p>
        </div>
        <a href="{{ route('templates.public') }}" class="btn btn-secondary shrink-0">
            View all templates <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($templates as $template)
            <a href="{{ route('register') }}" class="card card-hover overflow-hidden group">
                <div class="aspect-[16/10] bg-gradient-to-br from-brand-500/12 via-violet-500/8 to-cyan-400/10 relative overflow-hidden">
                    <div class="absolute inset-0 grid place-items-center">
                        <x-icon name="layout" class="w-12 h-12 text-brand-500/25" />
                    </div>
                    @if($template->is_premium)
                        <span class="absolute top-3 right-3"><x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /> Pro</x-ui.badge></span>
                    @endif
                    <div class="absolute inset-0 bg-ink-950/50 opacity-0 group-hover:opacity-100 transition-opacity grid place-items-center backdrop-blur-[2px]">
                        <span class="btn btn-primary btn-sm">Use template <x-icon name="arrow-right" class="w-3.5 h-3.5" /></span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="font-semibold text-sm truncate">{{ $template->name }}</h3>
                        <span class="text-[11px] text-tertiary capitalize shrink-0">{{ $template->category }}</span>
                    </div>
                    <p class="text-xs text-tertiary mt-1 line-clamp-2">{{ $template->description }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ══════════════ STATS ══════════════ --}}
<section class="max-w-7xl mx-auto px-5 sm:px-8 py-16">
    <div class="rounded-3xl mesh-bg bg-ink-950 text-white p-10 sm:p-14 relative overflow-hidden">
        <div class="relative z-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 text-center">
            @foreach([['48K+', 'Websites built'], ['2.4M', 'Pages published'], ['99.98%', 'Uptime SLA'], ['< 40s', 'Average build time']] as [$value, $label])
                <div>
                    <p class="text-4xl sm:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-white to-white/65 bg-clip-text text-transparent">{{ $value }}</p>
                    <p class="text-sm text-white/55 mt-2">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════ TESTIMONIALS ══════════════ --}}
<section class="max-w-7xl mx-auto px-5 sm:px-8 py-24">
    <div class="max-w-2xl mx-auto text-center mb-14">
        <x-ui.badge color="amber">Loved by builders</x-ui.badge>
        <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mt-5 leading-[1.1]">Teams ship faster here</h2>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @foreach([
            ['We replaced a six-week agency engagement with a forty-second prompt. The output needed almost no cleanup — it just looked right.', 'Sarah Chen', 'Head of Marketing, Northbeam', 'SC'],
            ['The visual editor is genuinely on par with Webflow, but the AI scaffolding means we start at 80% instead of a blank canvas.', 'Marcus Webb', 'Founder, Kestrel Studio', 'MW'],
            ['We manage 40+ client sites here. Custom domains, exports and the component library make it the only tool our team needs.', 'Priya Raman', 'Creative Director, Vantage', 'PR'],
        ] as $i => [$quote, $name, $role, $initials])
            <figure class="card p-6 flex flex-col">
                <div class="flex gap-0.5 mb-4">
                    @for($s = 0; $s < 5; $s++)<x-icon name="star" class="w-4 h-4 fill-amber-400 text-amber-400" />@endfor
                </div>
                <blockquote class="text-[15px] leading-relaxed text-secondary flex-1">"{{ $quote }}"</blockquote>
                <figcaption class="flex items-center gap-3 mt-6 pt-5 border-t border-subtle">
                    <span class="w-10 h-10 rounded-full grid place-items-center text-xs font-bold text-white shrink-0"
                          style="background-image:linear-gradient(135deg,hsl({{ $i*85+210 }} 72% 58%),hsl({{ $i*85+250 }} 72% 48%))">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">{{ $name }}</p>
                        <p class="text-xs text-tertiary truncate">{{ $role }}</p>
                    </div>
                </figcaption>
            </figure>
        @endforeach
    </div>
</section>

{{-- ══════════════ PRICING ══════════════ --}}
@if($plans->isNotEmpty())
<section class="max-w-7xl mx-auto px-5 sm:px-8 py-24" x-data="{ yearly: false }">
    <div class="max-w-2xl mx-auto text-center mb-12">
        <x-ui.badge color="sky">Pricing</x-ui.badge>
        <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mt-5 leading-[1.1]">Simple, honest pricing</h2>
        <p class="text-lg text-secondary mt-5">Start free. Upgrade when you are ready to launch.</p>

        <div class="inline-flex items-center gap-3 mt-8 p-1 rounded-xl bg-surface-muted border border-subtle">
            <button x-on:click="yearly = false" :class="!yearly ? 'bg-surface shadow-sm' : 'text-tertiary'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all">Monthly</button>
            <button x-on:click="yearly = true" :class="yearly ? 'bg-surface shadow-sm' : 'text-tertiary'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2">
                Yearly <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">−20%</span>
            </button>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-4 items-start">
        @foreach($plans as $plan)
            <div @class([
                'card p-6 relative flex flex-col h-full',
                'ring-2 ring-brand-500 shadow-glow lg:scale-[1.03] lg:-my-2' => $plan->is_featured,
                'card-hover' => !$plan->is_featured,
            ])>
                @if($plan->is_featured)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-r from-brand-500 to-violet-500 text-white text-[10px] font-bold uppercase tracking-wider shadow-lg shadow-brand-500/30">
                        Most popular
                    </span>
                @endif

                <h3 class="font-bold text-lg">{{ $plan->name }}</h3>
                <p class="text-xs text-tertiary mt-1 min-h-[2rem]">{{ $plan->description }}</p>

                <div class="mt-5 flex items-baseline gap-1">
                    <span class="text-4xl font-extrabold tracking-tight tabular-nums"
                          x-text="yearly ? '${{ number_format($plan->yearly_price / 12, 0) }}' : '${{ number_format($plan->monthly_price, 0) }}'"></span>
                    <span class="text-sm text-tertiary">/month</span>
                </div>
                <p class="text-[11px] text-tertiary mt-1 h-4" x-show="yearly" x-cloak>
                    ${{ number_format($plan->yearly_price, 0) }} billed annually
                </p>

                <a href="{{ route('register') }}" @class([
                    'btn w-full mt-6',
                    'btn-primary' => $plan->is_featured,
                    'btn-secondary' => !$plan->is_featured,
                ])>
                    {{ $plan->monthly_price > 0 ? 'Start '.$plan->trial_days.'-day trial' : 'Start free' }}
                </a>

                <ul class="space-y-2.5 mt-6 pt-6 border-t border-subtle flex-1">
                    @foreach(array_slice($plan->features ?? [], 0, 7) as $feature)
                        <li class="flex items-start gap-2.5 text-sm">
                            <x-icon name="check" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                            <span class="text-secondary leading-snug">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <p class="text-center text-sm text-tertiary mt-10">
        All plans include SSL, hosting and unlimited edits.
        <a href="{{ route('pricing') }}" class="text-brand-600 dark:text-brand-400 font-medium hover:underline">Compare all features →</a>
    </p>
</section>
@endif

{{-- ══════════════ FAQ ══════════════ --}}
@if($faqs->isNotEmpty())
<section class="max-w-3xl mx-auto px-5 sm:px-8 py-24">
    <div class="text-center mb-12">
        <x-ui.badge color="slate">FAQ</x-ui.badge>
        <h2 class="text-4xl font-extrabold tracking-tight mt-5">Questions, answered</h2>
    </div>

    <div class="space-y-3" x-data="{ open: 0 }">
        @foreach($faqs as $i => $faq)
            <div class="card overflow-hidden">
                <button x-on:click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-surface-muted transition-colors">
                    <span class="font-medium text-[15px]">{{ $faq->question }}</span>
                    <x-icon name="chevron-down" class="w-4 h-4 text-tertiary shrink-0 transition-transform duration-200"
                            ::class="open === {{ $i }} ? 'rotate-180' : ''" />
                </button>
                <div x-show="open === {{ $i }}" x-collapse x-cloak>
                    <p class="px-5 pb-5 text-sm text-secondary leading-relaxed">{{ $faq->answer }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <p class="text-center text-sm text-tertiary mt-10">
        Still curious? <a href="{{ route('contact') }}" class="text-brand-600 dark:text-brand-400 font-medium hover:underline">Talk to our team →</a>
    </p>
</section>
@endif

{{-- ══════════════ CTA ══════════════ --}}
<section class="max-w-7xl mx-auto px-5 sm:px-8 pb-24">
    <div class="rounded-3xl mesh-bg bg-ink-950 text-white p-12 sm:p-20 text-center relative overflow-hidden">
        <div class="relative z-10 max-w-2xl mx-auto">
            <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.08]">
                Your next website is<br>
                <span class="bg-gradient-to-r from-brand-300 via-violet-300 to-cyan-300 bg-clip-text text-transparent">one sentence away</span>
            </h2>
            <p class="text-lg text-white/65 mt-6">
                Join 48,000+ founders, marketers and agencies building faster with AI.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-9">
                <a href="{{ route('register') }}" class="btn btn-lg bg-white text-ink-950 hover:bg-white/90 w-full sm:w-auto shadow-xl">
                    Start building free <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
                <a href="{{ route('contact') }}" class="btn btn-lg bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/15 w-full sm:w-auto backdrop-blur">
                    Talk to sales
                </a>
            </div>
            <p class="text-xs text-white/40 mt-6">No credit card required · Free forever plan · Cancel anytime</p>
        </div>
    </div>
</section>

@endsection
