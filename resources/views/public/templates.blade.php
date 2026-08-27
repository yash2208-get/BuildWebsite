@extends('layouts.public')
@section('meta_title', 'Templates')
@section('meta_description', 'Professionally designed, fully responsive website templates. Free and premium.')

@section('content')
<section class="max-w-7xl mx-auto px-5 sm:px-8 pt-20 pb-12 text-center">
    <x-ui.badge color="emerald">Templates</x-ui.badge>
    <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight mt-5 leading-[1.05]">Start from something beautiful</h1>
    <p class="text-lg text-secondary mt-6 max-w-xl mx-auto">
        Every template is fully responsive, editable and yours to customise.
    </p>
</section>

<section class="max-w-7xl mx-auto px-5 sm:px-8 pb-8">
    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-8">
        <div class="relative flex-1">
            <x-icon name="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
            <input name="q" type="search" value="{{ request('q') }}" class="field pl-10" placeholder="Search templates…">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div class="flex flex-wrap gap-2 mb-10">
        <a href="{{ route('templates.public') }}"
           @class(['px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all border',
                   'bg-brand-500/12 border-brand-500/30 text-brand-600 dark:text-brand-300' => $category === 'all',
                   'border-subtle text-tertiary hover:text-secondary' => $category !== 'all'])>All</a>
        @foreach($categories as $key => $label)
            <a href="{{ route('templates.public', ['category' => $key]) }}"
               @class(['px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all border',
                       'bg-brand-500/12 border-brand-500/30 text-brand-600 dark:text-brand-300' => $category === $key,
                       'border-subtle text-tertiary hover:text-secondary' => $category !== $key])>{{ $label }}</a>
        @endforeach
    </div>

    @if($templates->isEmpty())
        <div class="card">
            <x-ui.empty-state icon="layout" title="No templates match your search"
                description="Try a different keyword or browse all categories.">
                <a href="{{ route('templates.public') }}" class="btn btn-secondary">Clear filters</a>
            </x-ui.empty-state>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($templates as $template)
                <a href="{{ route('register') }}" class="card card-hover overflow-hidden group">
                    <div class="aspect-[16/10] bg-gradient-to-br from-brand-500/12 via-violet-500/8 to-cyan-400/10 relative">
                        <div class="absolute inset-0 grid place-items-center"><x-icon name="layout" class="w-12 h-12 text-brand-500/25" /></div>
                        @if($template->is_premium)
                            <span class="absolute top-3 right-3"><x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /> Pro</x-ui.badge></span>
                        @endif
                        @if($template->is_featured)
                            <span class="absolute top-3 left-3"><x-ui.badge color="violet">Featured</x-ui.badge></span>
                        @endif
                        <div class="absolute inset-0 bg-ink-950/50 opacity-0 group-hover:opacity-100 transition-opacity grid place-items-center backdrop-blur-[2px]">
                            <span class="btn btn-primary btn-sm">Use this template <x-icon name="arrow-right" class="w-3.5 h-3.5" /></span>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-semibold text-sm truncate">{{ $template->name }}</h3>
                            <span class="text-[11px] text-tertiary capitalize shrink-0">{{ $template->category }}</span>
                        </div>
                        <p class="text-xs text-tertiary mt-1 line-clamp-2 min-h-[2rem]">{{ $template->description }}</p>
                        <div class="flex items-center gap-3 mt-3 pt-3 border-t border-subtle text-[11px] text-tertiary">
                            <span class="flex items-center gap-1"><x-icon name="file" class="w-3 h-3" /> {{ $template->pages_count ?? 1 }} pages</span>
                            <span class="flex items-center gap-1"><x-icon name="download" class="w-3 h-3" /> {{ number_format($template->uses_count ?? 0) }} uses</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">{{ $templates->links() }}</div>
    @endif
</section>

<section class="max-w-7xl mx-auto px-5 sm:px-8 py-20">
    <div class="rounded-3xl mesh-bg bg-ink-950 text-white p-12 sm:p-16 text-center">
        <h2 class="text-4xl font-extrabold tracking-tight">Or skip templates entirely</h2>
        <p class="text-white/65 mt-4 text-lg max-w-lg mx-auto">Describe your business and let AI generate a bespoke site built just for you.</p>
        <a href="{{ route('register') }}" class="btn btn-lg bg-white text-ink-950 hover:bg-white/90 mt-8 shadow-xl">
            <x-icon name="sparkles" class="w-4 h-4" /> Generate with AI
        </a>
    </div>
</section>
@endsection
