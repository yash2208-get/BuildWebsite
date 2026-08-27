@props(['padding' => 'p-6', 'hover' => false, 'title' => null, 'subtitle' => null, 'actions' => null])
<div {{ $attributes->class(['card', 'card-hover' => $hover]) }}>
    @if($title || $subtitle || $actions)
        <div class="flex items-start justify-between gap-4 px-6 pt-5 pb-4 border-b border-subtle">
            <div class="min-w-0">
                @if($title)<h3 class="font-semibold tracking-tight truncate">{{ $title }}</h3>@endif
                @if($subtitle)<p class="text-sm text-tertiary mt-0.5">{{ $subtitle }}</p>@endif
            </div>
            @if($actions)<div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>@endif
        </div>
        <div class="{{ $padding }}">{{ $slot }}</div>
    @else
        <div class="{{ $padding }}">{{ $slot }}</div>
    @endif
</div>
