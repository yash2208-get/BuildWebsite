@props(['title', 'description' => null, 'breadcrumbs' => []])

@php
    // Accept either ['Label' => '/url', ...] or [['label' => .., 'url' => ..], ...]
    $crumbs = collect($breadcrumbs)->map(function ($value, $key) {
        if (is_array($value)) {
            return ['label' => $value['label'] ?? '', 'url' => $value['url'] ?? null];
        }

        return ['label' => is_string($key) ? $key : $value, 'url' => is_string($key) ? $value : null];
    })->values();
@endphp

<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
    <div class="min-w-0">
        @if($crumbs->isNotEmpty())
            <nav class="flex items-center gap-1.5 text-xs text-tertiary mb-2" aria-label="Breadcrumb">
                @foreach($crumbs as $crumb)
                    @if(!$loop->first)<x-icon name="chevron-right" class="w-3 h-3" />@endif
                    @if($crumb['url'] && !$loop->last)
                        <a href="{{ $crumb['url'] }}" class="hover:text-brand-500 transition-colors">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-secondary font-medium">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="text-2xl font-bold tracking-tight">{{ $title }}</h1>
        @if($description)<p class="text-sm text-tertiary mt-1">{{ $description }}</p>@endif
    </div>

    @if(trim($slot) !== '')
        <div class="flex items-center gap-2 shrink-0 flex-wrap">{{ $slot }}</div>
    @endif
</div>
