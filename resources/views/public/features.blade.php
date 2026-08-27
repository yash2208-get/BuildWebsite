@extends('layouts.public')
@section('meta_title', 'Features')
@section('meta_description', 'AI generation, a visual drag-and-drop builder, responsive controls, SEO tools, custom domains and analytics.')

@section('content')
<section class="max-w-7xl mx-auto px-5 sm:px-8 pt-20 pb-16 text-center">
    <x-ui.badge color="violet">Features</x-ui.badge>
    <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight mt-5 leading-[1.05] max-w-4xl mx-auto">
        Everything you need to<br>
        <span class="bg-gradient-to-r from-brand-500 via-violet-500 to-cyan-400 bg-clip-text text-transparent">design, build and launch</span>
    </h1>
    <p class="text-lg text-secondary mt-6 max-w-2xl mx-auto">
        A professional-grade studio that replaces your page builder, design tool and hosting stack.
    </p>
</section>

@foreach([
    ['AI that actually designs', 'sparkles', 'brand', 'Describe your business once and receive a complete site — structure, copy, colour system and imagery direction. Every output is fully editable, never a locked black box.',
        ['Full multi-page website generation', 'Landing pages tuned for conversion', 'Long-form blog articles with SEO metadata', 'Accessible colour palettes (WCAG AA)', 'Art direction and image prompts', 'Design critique and layout advice']],
    ['A canvas, not a form', 'cursor', 'violet', 'Direct manipulation editing with layers, a full style manager and true responsive breakpoints. If you have used Webflow or Figma, you already know how to use this.',
        ['Drag, drop and nest any element', 'Layer tree with reordering', 'Complete CSS style manager', 'Desktop, laptop, tablet and mobile breakpoints', 'Undo/redo with full history', 'Autosave and version restore']],
    ['Blocks for every section', 'grid', 'emerald', 'A curated library of production-ready sections. Drop them in, restyle them to your brand, and move on.',
        ['Headers, navbars and footers', 'Heroes, features and pricing tables', 'Testimonials, teams and galleries', 'FAQs, accordions and tabs', 'Forms, maps and contact blocks', 'Sliders, carousels and countdowns']],
    ['Ship it properly', 'rocket', 'cyan', 'Publishing is not an afterthought. Custom domains, SSL, SEO metadata and analytics are built in from day one.',
        ['Free subdomain on every site', 'Custom domains with automatic SSL', 'Meta tags, Open Graph and sitemaps', 'Built-in traffic analytics', 'Custom CSS and JavaScript injection', 'Export clean, dependency-free HTML']],
] as $i => [$title, $icon, $color, $desc, $items])
    <section class="max-w-7xl mx-auto px-5 sm:px-8 py-16 {{ $i % 2 ? 'bg-surface-muted' : '' }}">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="{{ $i % 2 ? 'lg:order-2' : '' }}">
                <span class="w-12 h-12 rounded-2xl grid place-items-center mb-5 bg-{{ $color }}-500/12 text-{{ $color }}-500">
                    <x-icon :name="$icon" class="w-6 h-6" />
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight">{{ $title }}</h2>
                <p class="text-secondary mt-4 text-[15px] leading-relaxed">{{ $desc }}</p>
                <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-2.5 mt-7">
                    @foreach($items as $item)
                        <li class="flex items-start gap-2.5 text-sm">
                            <x-icon name="check-circle" class="w-4 h-4 text-{{ $color }}-500 shrink-0 mt-0.5" />
                            <span class="text-secondary leading-snug">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="{{ $i % 2 ? 'lg:order-1' : '' }}">
                <div class="card p-8 aspect-[4/3] grid place-items-center bg-gradient-to-br from-{{ $color }}-500/10 to-transparent relative overflow-hidden">
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,rgb(var(--border-subtle)/.4)_1px,transparent_1px),linear-gradient(to_bottom,rgb(var(--border-subtle)/.4)_1px,transparent_1px)] bg-[size:32px_32px]"></div>
                    <x-icon :name="$icon" class="w-24 h-24 text-{{ $color }}-500/25 relative" />
                </div>
            </div>
        </div>
    </section>
@endforeach

<section class="max-w-7xl mx-auto px-5 sm:px-8 py-20">
    <h2 class="text-3xl font-extrabold tracking-tight text-center mb-12">And a great deal more</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['palette', 'Theme builder'], ['image', 'Media manager'], ['search', 'SEO manager'],
            ['file', 'Forms builder'], ['book', 'Blog builder'], ['chart', 'Analytics'],
            ['code', 'Custom code'], ['download', 'HTML export'], ['users', 'Team roles'],
            ['shield', 'Enterprise security'], ['zap', 'REST API'], ['history', 'Version history'],
        ] as [$icon, $label])
            <div class="card card-hover p-5 flex items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-brand-500/10 text-brand-500 grid place-items-center shrink-0">
                    <x-icon :name="$icon" class="w-4 h-4" />
                </span>
                <span class="font-medium text-sm">{{ $label }}</span>
            </div>
        @endforeach
    </div>
</section>

<section class="max-w-7xl mx-auto px-5 sm:px-8 pb-20">
    <div class="rounded-3xl mesh-bg bg-ink-950 text-white p-12 sm:p-16 text-center">
        <h2 class="text-4xl font-extrabold tracking-tight">See it for yourself</h2>
        <p class="text-white/65 mt-4 text-lg">Build your first site free in under two minutes.</p>
        <a href="{{ route('register') }}" class="btn btn-lg bg-white text-ink-950 hover:bg-white/90 mt-8 shadow-xl">
            Start building free <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>
</section>
@endsection
