@props(['count' => 0, 'label' => 'item'])

@if($count > 0)
    <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-brand-500/8 ring-1 ring-brand-500/20 animate-[fade-up_.2s_ease-out_both]">
        <span class="text-sm font-medium">
            <span class="tabular-nums font-bold text-brand-600 dark:text-brand-400">{{ $count }}</span>
            {{ Str::plural($label, $count) }} selected
        </span>
        <div class="h-4 w-px bg-brand-500/25"></div>
        <div class="flex items-center gap-2 flex-wrap">{{ $slot }}</div>
        <button wire:click="clearSelection" class="ml-auto btn btn-ghost btn-sm">Clear</button>
    </div>
@endif
