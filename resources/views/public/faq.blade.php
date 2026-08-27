@extends('layouts.public')
@section('meta_title', 'Frequently asked questions')

@section('content')
<section class="max-w-3xl mx-auto px-5 sm:px-8 pt-20 pb-12 text-center">
    <x-ui.badge color="slate">Help centre</x-ui.badge>
    <h1 class="text-5xl font-extrabold tracking-tight mt-5">Frequently asked questions</h1>
    <p class="text-lg text-secondary mt-5">Everything you need to know about the platform.</p>
</section>

<section class="max-w-3xl mx-auto px-5 sm:px-8 pb-20">
    @forelse($groups as $category => $faqs)
        <div class="mb-10">
            <h2 class="text-xs font-bold uppercase tracking-[.12em] text-tertiary mb-4">{{ ucfirst($category ?: 'General') }}</h2>
            <div class="space-y-3" x-data="{ open: null }">
                @foreach($faqs as $i => $faq)
                    <div class="card overflow-hidden">
                        <button x-on:click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-surface-muted transition-colors">
                            <span class="font-medium text-[15px]">{{ $faq->question }}</span>
                            <x-icon name="chevron-down" class="w-4 h-4 text-tertiary shrink-0 transition-transform" ::class="open === {{ $i }} ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="px-5 pb-5 text-sm text-secondary leading-relaxed">{{ $faq->answer }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card"><x-ui.empty-state icon="help" title="No FAQs published yet" description="Check back soon." /></div>
    @endforelse

    <div class="card p-8 text-center mt-12">
        <span class="w-12 h-12 rounded-2xl bg-brand-500/12 text-brand-500 grid place-items-center mx-auto mb-4">
            <x-icon name="mail" class="w-6 h-6" />
        </span>
        <h3 class="font-semibold text-lg">Still have a question?</h3>
        <p class="text-sm text-secondary mt-2">Our team usually replies within one business day.</p>
        <a href="{{ route('contact') }}" class="btn btn-primary mt-5">Contact support</a>
    </div>
</section>
@endsection
