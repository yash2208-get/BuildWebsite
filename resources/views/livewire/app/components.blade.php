<div class="space-y-6">
    <x-ui.page-header title="Component Library" :description="$total.' ready-to-use blocks you can drop into any page.'" />

    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <x-icon name="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
            <input wire:model.live.debounce.300ms="search" type="search" class="field pl-10" placeholder="Search blocks…">
        </div>
        <select wire:model.live="category" class="field sm:w-56">
            @foreach($categories as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
    </div>

    @forelse($groups as $groupKey => $items)
        <div>
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-xs font-bold uppercase tracking-[.12em] text-tertiary">{{ $categories[$groupKey] ?? ucfirst($groupKey) }}</h2>
                <span class="text-[11px] text-tertiary tabular-nums">{{ $items->count() }}</span>
                <div class="flex-1 h-px bg-[rgb(var(--border-subtle))]"></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($items as $block)
                    <div wire:key="cmp-{{ $block->id }}" class="card card-hover p-5 group">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500/15 to-cyan-400/10 grid place-items-center text-lg shrink-0">
                                {{ $block->icon ?: '▦' }}
                            </span>
                            @if($block->is_premium)<x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /></x-ui.badge>@endif
                        </div>
                        <h3 class="font-semibold text-sm">{{ $block->name }}</h3>
                        <p class="text-xs text-tertiary mt-1 line-clamp-2 min-h-[2rem]">{{ $block->description }}</p>
                        <button wire:click="$set('previewing', {{ $block->id }})"
                                class="btn btn-secondary btn-sm w-full mt-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <x-icon name="eye" class="w-3.5 h-3.5" /> Preview
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card">
            <x-ui.empty-state icon="grid" title="No blocks match your search" description="Try another keyword or category.">
                <button wire:click="$set('search','');$set('category','all')" class="btn btn-secondary">Clear filters</button>
            </x-ui.empty-state>
        </div>
    @endforelse

    <div class="rounded-2xl p-6 bg-gradient-to-br from-brand-500/10 via-violet-500/8 to-cyan-400/8 ring-1 ring-brand-500/15 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <span class="w-11 h-11 rounded-xl bg-brand-500/15 text-brand-500 grid place-items-center shrink-0">
            <x-icon name="cursor" class="w-5 h-5" />
        </span>
        <div class="flex-1">
            <p class="font-semibold text-sm">Use these blocks in the builder</p>
            <p class="text-sm text-secondary mt-1">Open any website and drag blocks straight from the left panel onto your canvas.</p>
        </div>
        <a href="{{ route('app.websites') }}" class="btn btn-primary shrink-0">Open a website</a>
    </div>

    @if($preview)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('previewing', null)"></div>
            <div class="relative card shadow-2xl max-w-4xl w-full max-h-[85vh] flex flex-col animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-subtle">
                    <div>
                        <h3 class="font-semibold text-lg">{{ $preview->name }}</h3>
                        <p class="text-sm text-tertiary mt-0.5">{{ $preview->description }}</p>
                    </div>
                    <button wire:click="$set('previewing', null)" class="btn btn-ghost btn-icon shrink-0"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <div class="flex-1 overflow-auto scrollbar-thin p-6 bg-surface-muted">
                    <div class="rounded-xl bg-white overflow-hidden shadow-lg">
                        <iframe class="w-full h-[420px] border-0" srcdoc="{{ $preview->html }}" sandbox="allow-same-origin" title="Block preview"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
