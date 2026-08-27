@extends('layouts.public')
@section('meta_title', $page->seo['title'] ?? $page->title)
@section('meta_description', $page->seo['description'] ?? '')

@section('content')
<article class="max-w-3xl mx-auto px-5 sm:px-8 pt-20 pb-24">
    <header class="mb-10 pb-8 border-b border-subtle">
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.08]">{{ $page->title }}</h1>
        @if($page->updated_at)
            <p class="text-sm text-tertiary mt-4">Last updated {{ $page->updated_at->format('F j, Y') }}</p>
        @endif
    </header>

    <div class="space-y-5 leading-relaxed text-secondary
                [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-primary [&_h2]:mt-10 [&_h2]:mb-3
                [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-primary [&_h3]:mt-7 [&_h3]:mb-2
                [&_p]:leading-relaxed
                [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1.5
                [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1.5
                [&_a]:text-brand-600 dark:[&_a]:text-brand-400 [&_a]:underline
                [&_blockquote]:border-l-2 [&_blockquote]:border-brand-500 [&_blockquote]:pl-4 [&_blockquote]:italic
                [&_code]:font-mono [&_code]:text-sm [&_code]:bg-surface-muted [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">
        {!! $page->content !!}
    </div>
</article>
@endsection
