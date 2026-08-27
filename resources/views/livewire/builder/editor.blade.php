@php
    $blockDefs = [];
    foreach ($blocks as $category => $items) {
        foreach ($items as $block) {
            $blockDefs[] = [
                'id' => 'block-'.$block->id,
                'label' => '<div class="ab-block"><span class="ab-block-ico">'.($block->icon ?: '▦').'</span><span>'.e($block->name).'</span></div>',
                'category' => $categories[$category] ?? ucfirst($category),
                'content' => $block->html,
                'attributes' => ['title' => $block->description],
            ];
        }
    }
@endphp

<div class="h-screen flex flex-col"
     x-data="builderEditor({
        content: @js($this->canvasPayload()),
        blocks: @js($blockDefs),
        lastSavedAt: @js($lastSavedAt),
     })">

    {{-- ══════════════ TOP BAR ══════════════ --}}
    <header class="h-14 shrink-0 flex items-center gap-3 px-3 bg-surface border-b border-subtle z-30">
        {{-- left --}}
        <div class="flex items-center gap-2 min-w-0">
            <a href="{{ route('app.websites') }}" class="btn btn-ghost btn-icon shrink-0" title="Back to websites">
                <x-icon name="arrow-left" class="w-4 h-4" />
            </a>
            <div class="min-w-0 hidden sm:block">
                <p class="text-sm font-semibold truncate leading-tight">{{ $website->name }}</p>
                <button wire:click="$toggle('showPages')" class="text-xs text-tertiary hover:text-secondary flex items-center gap-1 leading-tight">
                    {{ $page->title }} <x-icon name="chevron-down" class="w-3 h-3" />
                </button>
            </div>
        </div>

        <div class="h-6 w-px bg-[rgb(var(--border-subtle))] mx-1 hidden sm:block"></div>

        {{-- history --}}
        <div class="flex items-center gap-0.5">
            <button x-on:click="undo()" class="btn btn-ghost btn-icon" title="Undo (⌘Z)"><x-icon name="undo" class="w-4 h-4" /></button>
            <button x-on:click="redo()" class="btn btn-ghost btn-icon" title="Redo (⌘⇧Z)"><x-icon name="redo" class="w-4 h-4" /></button>
        </div>

        {{-- devices --}}
        <div class="flex items-center gap-0.5 p-1 rounded-xl bg-surface-muted mx-auto">
            @foreach([['desktop','monitor','Desktop'],['laptop','laptop','Laptop'],['tablet','tablet','Tablet'],['mobile','phone','Mobile']] as [$d, $icon, $label])
                <button x-on:click="setDevice('{{ $d }}')"
                        :class="device === '{{ $d }}' ? 'bg-surface shadow-sm text-brand-600 dark:text-brand-300' : 'text-tertiary hover:text-secondary'"
                        class="btn-icon rounded-lg transition-all" title="{{ $label }}">
                    <x-icon :name="$icon" class="w-4 h-4" />
                </button>
            @endforeach
        </div>

        {{-- right --}}
        <div class="flex items-center gap-2 ml-auto">
            <span class="text-xs text-tertiary hidden lg:flex items-center gap-1.5">
                <template x-if="saving">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3 h-3 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>
                            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg> Saving…
                    </span>
                </template>
                <template x-if="!saving && dirty"><span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Unsaved changes</span></template>
                <template x-if="!saving && !dirty"><span class="flex items-center gap-1.5"><x-icon name="check" class="w-3 h-3 text-emerald-500" /> <span x-text="savedLabel"></span></span></template>
            </span>

            <button x-on:click="togglePreview()" class="btn btn-ghost btn-icon" title="Preview"><x-icon name="eye" class="w-4 h-4" /></button>
            <button x-on:click="toggleCode()" class="btn btn-ghost btn-icon" title="View code"><x-icon name="code" class="w-4 h-4" /></button>
            <button wire:click="$toggle('showRevisions')" class="btn btn-ghost btn-icon" title="Version history"><x-icon name="history" class="w-4 h-4" /></button>

            <div class="h-6 w-px bg-[rgb(var(--border-subtle))]"></div>

            <button x-on:click="save()" class="btn btn-secondary btn-sm" :disabled="saving">
                <x-icon name="save" class="w-3.5 h-3.5" /> <span class="hidden sm:inline">Save</span>
            </button>

            @if($website->isPublished())
                <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                    <button x-on:click="open = !open" class="btn btn-primary btn-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span> Live
                        <x-icon name="chevron-down" class="w-3 h-3" />
                    </button>
                    <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-56 card shadow-xl z-40 p-1.5 text-sm">
                        <a href="{{ route('site.preview', $website->subdomain) }}" target="_blank" class="nav-link"><x-icon name="external" class="w-4 h-4" /> View live site</a>
                        <button wire:click="publish" x-on:click="open=false" class="nav-link w-full"><x-icon name="refresh" class="w-4 h-4" /> Re-publish changes</button>
                        <div class="divider my-1"></div>
                        <button wire:click="unpublish" x-on:click="open=false" class="nav-link w-full text-rose-600 dark:text-rose-400"><x-icon name="eye-off" class="w-4 h-4" /> Unpublish</button>
                    </div>
                </div>
            @else
                <button wire:click="publish" class="btn btn-primary btn-sm">
                    <x-icon name="rocket" class="w-3.5 h-3.5" /> Publish
                </button>
            @endif
        </div>
    </header>

    {{-- ══════════════ BODY ══════════════ --}}
    <div class="flex-1 flex min-h-0">

        {{-- ────── LEFT PANEL ────── --}}
        <aside class="w-72 shrink-0 bg-surface border-r border-subtle flex flex-col">
            <div class="flex border-b border-subtle">
                @foreach([['blocks','grid','Blocks'],['layers','layers','Layers'],['pages','file','Pages']] as [$p, $icon, $label])
                    <button x-on:click="setPanel('{{ $p }}')"
                            :class="panel === '{{ $p }}' ? 'text-brand-600 dark:text-brand-300 border-brand-500' : 'text-tertiary border-transparent hover:text-secondary'"
                            class="flex-1 flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold border-b-2 transition-colors">
                        <x-icon :name="$icon" class="w-4 h-4" /> {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex-1 overflow-y-auto scrollbar-thin">
                {{-- blocks --}}
                <div x-show="panel === 'blocks'" class="gjs-blocks-host p-2" x-ref="blocks"></div>

                {{-- layers --}}
                <div x-show="panel === 'layers'" x-cloak class="gjs-layers-host" x-ref="layers"></div>

                {{-- pages --}}
                <div x-show="panel === 'pages'" x-cloak class="p-3 space-y-1">
                    @foreach($pages as $p)
                        <div wire:key="page-{{ $p->id }}"
                             @class([
                                'group flex items-center gap-2 px-2.5 py-2 rounded-lg text-sm transition-colors',
                                'bg-brand-500/12 text-brand-700 dark:text-brand-300 font-medium' => $p->id === $page->id,
                                'hover:bg-surface-muted' => $p->id !== $page->id,
                             ])>
                            <button wire:click="switchPage({{ $p->id }})" class="flex items-center gap-2 flex-1 min-w-0 text-left">
                                <x-icon :name="$p->is_homepage ? 'home' : 'file'" class="w-4 h-4 shrink-0 {{ $p->is_homepage ? 'text-amber-500' : 'text-tertiary' }}" />
                                <span class="truncate">{{ $p->title }}</span>
                            </button>
                            <div class="relative shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" x-data="{ open: false }" x-on:click.outside="open = false">
                                <button x-on:click="open = !open" class="p-1 rounded hover:bg-surface-raised"><x-icon name="more" class="w-3.5 h-3.5" /></button>
                                <div x-show="open" x-cloak x-transition class="absolute right-0 mt-1 w-44 card shadow-xl z-40 p-1 text-xs">
                                    <button wire:click="duplicatePage({{ $p->id }})" x-on:click="open=false" class="nav-link w-full text-xs py-1.5"><x-icon name="copy" class="w-3.5 h-3.5" /> Duplicate</button>
                                    @unless($p->is_homepage)
                                        <button wire:click="setHomepage({{ $p->id }})" x-on:click="open=false" class="nav-link w-full text-xs py-1.5"><x-icon name="home" class="w-3.5 h-3.5" /> Set as homepage</button>
                                        <button wire:click="deletePage({{ $p->id }})" x-on:click="open=false" class="nav-link w-full text-xs py-1.5 text-rose-500"><x-icon name="trash" class="w-3.5 h-3.5" /> Delete</button>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <form wire:submit="createPage" class="pt-3 mt-3 border-t border-subtle space-y-2">
                        <input wire:model="newPageTitle" type="text" class="field text-sm py-2" placeholder="New page title…">
                        @error('newPageTitle')<p class="error-text">{{ $message }}</p>@enderror
                        <button type="submit" class="btn btn-secondary btn-sm w-full"><x-icon name="plus" class="w-3.5 h-3.5" /> Add page</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ────── CANVAS ────── --}}
        <main class="flex-1 min-w-0 bg-ink-100 dark:bg-ink-950 relative">
            <div x-ref="canvas" class="h-full w-full"></div>
        </main>

        {{-- ────── RIGHT PANEL ────── --}}
        <aside class="w-72 shrink-0 bg-surface border-l border-subtle flex flex-col" x-data="{ right: 'styles' }">
            <div class="flex border-b border-subtle">
                @foreach([['styles','palette','Styles'],['traits','sliders','Settings']] as [$r, $icon, $label])
                    <button x-on:click="right = '{{ $r }}'"
                            :class="right === '{{ $r }}' ? 'text-brand-600 dark:text-brand-300 border-brand-500' : 'text-tertiary border-transparent hover:text-secondary'"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[11px] font-semibold border-b-2 transition-colors">
                        <x-icon :name="$icon" class="w-4 h-4" /> {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="flex-1 overflow-y-auto scrollbar-thin">
                <div x-show="right === 'styles'" class="gjs-styles-host" x-ref="styles"></div>
                <div x-show="right === 'traits'" x-cloak class="gjs-traits-host" x-ref="traits"></div>
            </div>
        </aside>
    </div>

    {{-- ══════════════ REVISIONS DRAWER ══════════════ --}}
    @if($showRevisions)
        <div class="fixed inset-0 z-50 flex justify-end">
            <div class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm" wire:click="$set('showRevisions', false)"></div>
            <div class="relative w-96 max-w-full h-full bg-surface border-l border-subtle shadow-2xl flex flex-col animate-[slide-left_.28s_cubic-bezier(.16,1,.3,1)_both]">
                <div class="flex items-center justify-between px-5 py-4 border-b border-subtle">
                    <div>
                        <p class="font-semibold">Version history</p>
                        <p class="text-xs text-tertiary mt-0.5">{{ $page->title }}</p>
                    </div>
                    <button wire:click="$set('showRevisions', false)" class="btn btn-ghost btn-icon"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <div class="flex-1 overflow-y-auto scrollbar-thin">
                    @forelse($revisions as $rev)
                        <div class="px-5 py-3.5 border-b border-subtle hover:bg-surface-muted transition-colors group">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium">Version {{ $rev->version }}</p>
                                    <p class="text-xs text-tertiary mt-0.5">
                                        {{ $rev->user?->name ?? 'System' }} · {{ $rev->created_at->diffForHumans() }}
                                    </p>
                                    @if($rev->note)<p class="text-xs text-secondary mt-1">{{ $rev->note }}</p>@endif
                                </div>
                                <button wire:click="restoreRevision({{ $rev->id }})"
                                        class="btn btn-secondary btn-sm shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Restore
                                </button>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state compact icon="history" title="No revisions yet"
                            description="Versions are captured automatically each time you save." />
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

@push('head')
<style>
    /* GrapesJS theme bridge — maps the editor chrome onto our design tokens. */
    .gjs-one-bg { background-color: rgb(var(--surface)); }
    .gjs-two-color { color: rgb(var(--text-primary)); }
    .gjs-three-bg { background-color: rgb(var(--brand-500, 99 102 241)); color: #fff; }
    .gjs-four-color, .gjs-four-color-h:hover { color: #6366f1; }

    .gjs-blocks-host .gjs-blocks-c { display: grid; grid-template-columns: repeat(2, 1fr); gap: .5rem; padding: .25rem; }
    .gjs-blocks-host .gjs-block {
        width: auto; min-height: auto; margin: 0; padding: .75rem .5rem;
        border-radius: .75rem; border: 1px solid rgb(var(--border-subtle));
        background: rgb(var(--surface-muted)); box-shadow: none;
        transition: all .18s cubic-bezier(.16,1,.3,1); font-size: 11px; font-weight: 500;
    }
    .gjs-blocks-host .gjs-block:hover {
        border-color: rgb(99 102 241 / .45); background: rgb(99 102 241 / .07);
        transform: translateY(-2px); box-shadow: 0 6px 16px -6px rgb(99 102 241 / .35);
    }
    .gjs-blocks-host .gjs-block-label { font-size: 11px; line-height: 1.3; color: rgb(var(--text-secondary)); }
    .gjs-blocks-host .gjs-block-category .gjs-title {
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: rgb(var(--text-tertiary)); background: transparent; border: 0; padding: .75rem .5rem .35rem;
    }
    .ab-block { display: flex; flex-direction: column; align-items: center; gap: .375rem; }
    .ab-block-ico { font-size: 18px; line-height: 1; }

    .gjs-sm-sector-title, .gjs-trt-header, .gjs-clm-tags-label {
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: rgb(var(--text-tertiary)); background: transparent;
        border-bottom: 1px solid rgb(var(--border-subtle)); padding: .625rem .875rem;
    }
    .gjs-sm-property, .gjs-trt-trait { padding: .375rem .875rem; }
    .gjs-sm-label, .gjs-trt-trait__label, .gjs-label { font-size: 11px; color: rgb(var(--text-secondary)); }
    .gjs-field {
        background: rgb(var(--surface-muted)); border: 1px solid rgb(var(--border-subtle));
        border-radius: .5rem; color: rgb(var(--text-primary)); font-size: 12px;
    }
    .gjs-field:focus-within { border-color: rgb(99 102 241 / .5); box-shadow: 0 0 0 3px rgb(99 102 241 / .12); }
    .gjs-field input, .gjs-field select, .gjs-field textarea { color: rgb(var(--text-primary)); font-size: 12px; }

    .gjs-layer { border-bottom: 1px solid rgb(var(--border-subtle)); }
    .gjs-layer-title { font-size: 12px; padding: .5rem .625rem; }
    .gjs-layer.gjs-selected > .gjs-layer-item { background: rgb(99 102 241 / .12); }

    .gjs-cv-canvas { background: transparent; top: 0; height: 100%; width: 100%; }
    .gjs-cv-canvas__frames { background: transparent; }
    .gjs-frame-wrapper { padding: 1.25rem; }
    .gjs-frame { border-radius: .75rem; box-shadow: 0 12px 40px -12px rgb(15 23 42 / .28); background: #fff; }
    .gjs-badge, .gjs-toolbar { background: #6366f1; border-radius: .375rem; font-size: 10px; }
    .gjs-resizer-h { border-color: #6366f1; }
    .gjs-placeholder { border-color: #6366f1; }
    .gjs-placeholder.horizontal { border-top-color: #6366f1; }
</style>
@endpush
