@props(['placeholder' => 'Search…', 'showPerPage' => true])

<div class="flex flex-col lg:flex-row gap-3 lg:items-center">
    <div class="relative flex-1 min-w-0">
        <x-icon name="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
        <input wire:model.live.debounce.350ms="search" type="search" class="field pl-10" placeholder="{{ $placeholder }}">
        <div wire:loading wire:target="search" class="absolute right-3.5 top-1/2 -translate-y-1/2">
            <svg class="w-4 h-4 animate-spin text-brand-500" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    {{ $slot }}

    @if($showPerPage)
        <select wire:model.live="perPage" class="field w-auto shrink-0">
            @foreach([10, 25, 50, 100] as $n)<option value="{{ $n }}">{{ $n }} / page</option>@endforeach
        </select>
    @endif
</div>
