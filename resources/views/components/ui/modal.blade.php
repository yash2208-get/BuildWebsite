@props(['name' => null, 'title' => null, 'maxWidth' => 'max-w-lg', 'show' => 'open'])
<div x-show="{{ $show }}" x-cloak
     class="fixed inset-0 z-[60] overflow-y-auto"
     x-on:keydown.escape.window="{{ $show }} = false"
     role="dialog" aria-modal="true">
    <div x-show="{{ $show }}" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" x-on:click="{{ $show }} = false"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div x-show="{{ $show }}"
             x-transition:enter="transition ease-out duration-220"
             x-transition:enter-start="opacity-0 translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full {{ $maxWidth }} card shadow-2xl">
            @if($title)
                <div class="flex items-center justify-between px-6 py-4 border-b border-subtle">
                    <h3 class="font-semibold tracking-tight">{{ $title }}</h3>
                    <button type="button" class="btn btn-ghost btn-icon" x-on:click="{{ $show }} = false" aria-label="Close">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            @endif
            <div class="p-6">{{ $slot }}</div>
            @isset($footer)
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-subtle bg-surface-muted rounded-b-2xl">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
