<div class="space-y-6">
    <x-ui.page-header title="Templates" description="Launch a new site from a professionally designed starting point." />

    <div class="flex flex-col lg:flex-row gap-3">
        <div class="relative flex-1">
            <x-icon name="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
            <input wire:model.live.debounce.350ms="search" type="search" class="field pl-10" placeholder="Search templates…">
        </div>
        <select wire:model.live="category" class="field lg:w-56">
            @foreach($categories as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <label class="flex items-center gap-2.5 px-4 rounded-xl border border-subtle bg-surface cursor-pointer select-none whitespace-nowrap">
            <input type="checkbox" wire:model.live="freeOnly" class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4">
            <span class="text-sm text-secondary">Free only</span>
        </label>
    </div>

    <div wire:loading.delay.longer><x-ui.skeleton variant="cards" :rows="6" /></div>

    <div wire:loading.remove.delay.longer>
        @if($templates->isEmpty())
            <div class="card">
                <x-ui.empty-state icon="layout" title="No templates found" description="Try a different search term or category.">
                    <button wire:click="$set('search','');$set('category','all')" class="btn btn-secondary">Clear filters</button>
                </x-ui.empty-state>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 stagger">
                @foreach($templates as $template)
                    <div wire:key="tpl-{{ $template->id }}" class="card card-hover overflow-hidden group flex flex-col">
                        <div class="aspect-[16/10] bg-gradient-to-br from-brand-500/12 via-violet-500/8 to-cyan-400/10 relative">
                            <div class="absolute inset-0 grid place-items-center"><x-icon name="layout" class="w-12 h-12 text-brand-500/25" /></div>
                            <div class="absolute top-3 left-3 flex gap-1.5">
                                @if($template->is_featured)<x-ui.badge color="violet">Featured</x-ui.badge>@endif
                                @if($template->is_premium)<x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /> Pro</x-ui.badge>@endif
                            </div>
                            <div class="absolute inset-0 bg-ink-950/55 opacity-0 group-hover:opacity-100 transition-opacity grid place-items-center gap-2 backdrop-blur-[2px]">
                                <button wire:click="useTemplate({{ $template->id }})" class="btn btn-primary btn-sm">
                                    <x-icon name="plus" class="w-3.5 h-3.5" /> Use template
                                </button>
                                <button wire:click="$set('previewing', {{ $template->id }})" class="btn btn-sm bg-white/15 text-white ring-1 ring-white/25 hover:bg-white/25">
                                    <x-icon name="eye" class="w-3.5 h-3.5" /> Preview
                                </button>
                            </div>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-sm truncate">{{ $template->name }}</h3>
                                <span class="text-[11px] text-tertiary capitalize shrink-0">{{ $template->category }}</span>
                            </div>
                            <p class="text-xs text-tertiary mt-1 line-clamp-2 flex-1">{{ $template->description }}</p>
                            <div class="flex items-center gap-3 mt-3 pt-3 border-t border-subtle text-[11px] text-tertiary">
                                <span class="flex items-center gap-1"><x-icon name="file" class="w-3 h-3" /> {{ $template->pages_count ?? 1 }} pages</span>
                                <span class="flex items-center gap-1"><x-icon name="download" class="w-3 h-3" /> {{ number_format($template->uses_count ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($templates->hasPages())<div class="mt-6">{{ $templates->links() }}</div>@endif
        @endif
    </div>

    @if($preview)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('previewing', null)"></div>
            <div class="relative card shadow-2xl max-w-3xl w-full animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both] overflow-hidden">
                <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-subtle">
                    <div>
                        <h3 class="font-semibold text-lg">{{ $preview->name }}</h3>
                        <p class="text-sm text-tertiary mt-0.5">{{ $preview->description }}</p>
                    </div>
                    <button wire:click="$set('previewing', null)" class="btn btn-ghost btn-icon shrink-0"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <div class="aspect-[16/9] bg-gradient-to-br from-brand-500/12 to-cyan-400/8 grid place-items-center">
                    <x-icon name="layout" class="w-20 h-20 text-brand-500/25" />
                </div>
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-subtle">
                    <div class="flex items-center gap-2">
                        <x-ui.badge color="slate">{{ ucfirst($preview->category) }}</x-ui.badge>
                        @if($preview->is_premium)<x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /> Premium</x-ui.badge>@endif
                    </div>
                    <button wire:click="useTemplate({{ $preview->id }})" class="btn btn-primary">
                        <x-icon name="plus" class="w-4 h-4" /> Use this template
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
