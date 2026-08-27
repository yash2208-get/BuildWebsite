<div class="space-y-6" x-data="{ createOpen: @entangle('showCreate') }">

    <x-ui.page-header title="My Websites" description="Create, edit and publish your sites.">
        <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
            <button wire:click="$set('view','grid')" @class(['btn-icon rounded-lg transition-all', 'bg-surface shadow-sm text-brand-600 dark:text-brand-300' => $view==='grid', 'text-tertiary' => $view!=='grid'])>
                <x-icon name="grid" class="w-4 h-4" />
            </button>
            <button wire:click="$set('view','list')" @class(['btn-icon rounded-lg transition-all', 'bg-surface shadow-sm text-brand-600 dark:text-brand-300' => $view==='list', 'text-tertiary' => $view!=='list'])>
                <x-icon name="list" class="w-4 h-4" />
            </button>
        </div>
        <a href="{{ route('app.ai') }}" class="btn btn-secondary"><x-icon name="sparkles" class="w-4 h-4" /> AI generate</a>
        <button x-on:click="createOpen = true" class="btn btn-primary" @disabled(!$canCreate)>
            <x-icon name="plus" class="w-4 h-4" /> New website
        </button>
    </x-ui.page-header>

    @unless($canCreate)
        <div class="rounded-2xl bg-amber-500/10 ring-1 ring-amber-500/20 px-5 py-4 flex items-start gap-3">
            <x-icon name="zap" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
            <div class="flex-1">
                <p class="font-semibold text-sm text-amber-700 dark:text-amber-300">Website limit reached</p>
                <p class="text-sm text-amber-700/80 dark:text-amber-300/80 mt-0.5">
                    Your {{ $this->user()->activePlan()?->name ?? 'Free' }} plan is at capacity. Upgrade to create more sites.
                </p>
            </div>
            <a href="{{ route('app.billing') }}" class="btn btn-primary btn-sm shrink-0">Upgrade</a>
        </div>
    @endunless

    {{-- stats --}}
    <div class="grid gap-4 sm:grid-cols-4">
        <x-ui.stat label="Total" :value="$stats['total']" icon="globe" color="brand" />
        <x-ui.stat label="Published" :value="$stats['published']" icon="rocket" color="emerald" />
        <x-ui.stat label="Drafts" :value="$stats['draft']" icon="edit" color="amber" />
        <x-ui.stat label="Total views" :value="number_format($stats['views'])" icon="chart" color="sky" />
    </div>

    {{-- filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <x-icon name="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
            <input wire:model.live.debounce.350ms="search" type="search" placeholder="Search websites…" class="field pl-10">
        </div>
        <select wire:model.live="status" class="field sm:w-48">
            <option value="all">All statuses</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    {{-- loading --}}
    <div wire:loading.delay.longer class="w-full">
        <x-ui.skeleton variant="cards" :rows="6" />
    </div>

    <div wire:loading.remove.delay.longer>
        @if($websites->isEmpty())
            <div class="card">
                <x-ui.empty-state icon="globe"
                    :title="$search || $status !== 'all' ? 'No websites match your filters' : 'Create your first website'"
                    :description="$search || $status !== 'all' ? 'Try adjusting your search or filters.' : 'Start from scratch, pick a template, or describe your idea and let AI build it.'">
                    @if($search || $status !== 'all')
                        <button wire:click="$set('search','');$set('status','all')" class="btn btn-secondary">Clear filters</button>
                    @else
                        <a href="{{ route('app.ai') }}" class="btn btn-primary"><x-icon name="sparkles" class="w-4 h-4" /> Generate with AI</a>
                        <a href="{{ route('app.templates') }}" class="btn btn-secondary">Browse templates</a>
                    @endif
                </x-ui.empty-state>
            </div>

        @elseif($view === 'grid')
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 stagger">
                @foreach($websites as $site)
                    <div wire:key="site-{{ $site->id }}" class="card card-hover overflow-hidden group flex flex-col">
                        {{-- preview --}}
                        <a href="{{ route('builder.edit', $site) }}" class="relative block aspect-[16/10] bg-gradient-to-br from-brand-500/12 via-violet-500/8 to-cyan-400/10 overflow-hidden">
                            <div class="absolute inset-0 grid place-items-center">
                                <x-icon name="globe" class="w-12 h-12 text-brand-500/30" />
                            </div>
                            <div class="absolute top-3 left-3">
                                <x-ui.badge :color="$site->status->color()" dot>{{ $site->status->label() }}</x-ui.badge>
                            </div>
                            <div class="absolute inset-0 bg-ink-950/55 opacity-0 group-hover:opacity-100 transition-opacity grid place-items-center backdrop-blur-[2px]">
                                <span class="btn btn-primary btn-sm"><x-icon name="edit" class="w-3.5 h-3.5" /> Open builder</span>
                            </div>
                        </a>

                        <div class="p-4 flex-1 flex flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-semibold truncate">{{ $site->name }}</h3>
                                    <p class="text-xs text-tertiary truncate mt-0.5">{{ $site->display_domain }}</p>
                                </div>

                                <div class="relative shrink-0" x-data="{ open: false }" x-on:click.outside="open = false">
                                    <button class="btn btn-ghost btn-icon" x-on:click="open = !open" aria-label="Actions">
                                        <x-icon name="more" class="w-4 h-4" />
                                    </button>
                                    <div x-show="open" x-cloak x-transition.origin.top.right
                                         class="absolute right-0 mt-1 w-48 card shadow-xl z-20 py-1 text-sm">
                                        <a href="{{ route('builder.edit', $site) }}" class="nav-link rounded-none px-3 py-2"><x-icon name="edit" class="w-4 h-4" /> Edit</a>
                                        <a href="{{ route('site.preview', $site->subdomain) }}" target="_blank" class="nav-link rounded-none px-3 py-2"><x-icon name="external" class="w-4 h-4" /> Preview</a>
                                        <a href="{{ route('app.website.settings', $site) }}" class="nav-link rounded-none px-3 py-2"><x-icon name="settings" class="w-4 h-4" /> Settings</a>
                                        <button wire:click="clone({{ $site->id }})" x-on:click="open=false" class="nav-link rounded-none px-3 py-2 w-full"><x-icon name="copy" class="w-4 h-4" /> Duplicate</button>
                                        @can('export', $site)
                                            <a href="{{ route('website.export', $site) }}" class="nav-link rounded-none px-3 py-2"><x-icon name="download" class="w-4 h-4" /> Export HTML</a>
                                        @endcan
                                        <div class="divider my-1"></div>
                                        <button wire:click="$set('confirmingDelete', {{ $site->id }})" x-on:click="open=false"
                                                class="nav-link rounded-none px-3 py-2 w-full text-rose-600 dark:text-rose-400 hover:bg-rose-500/10">
                                            <x-icon name="trash" class="w-4 h-4" /> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 mt-3 text-xs text-tertiary">
                                <span class="flex items-center gap-1"><x-icon name="file" class="w-3.5 h-3.5" /> {{ $site->pages_count }}</span>
                                <span class="flex items-center gap-1"><x-icon name="eye" class="w-3.5 h-3.5" /> {{ number_format($site->views_count) }}</span>
                                <span class="ml-auto">{{ $site->updated_at->diffForHumans(short: true) }}</span>
                            </div>

                            <div class="flex items-center gap-2 mt-4 pt-3 border-t border-subtle">
                                <a href="{{ route('builder.edit', $site) }}" class="btn btn-secondary btn-sm flex-1">
                                    <x-icon name="edit" class="w-3.5 h-3.5" /> Edit
                                </a>
                                <button wire:click="togglePublish({{ $site->id }})"
                                        class="btn btn-sm flex-1 {{ $site->isPublished() ? 'btn-ghost' : 'btn-primary' }}">
                                    <x-icon :name="$site->isPublished() ? 'eye' : 'rocket'" class="w-3.5 h-3.5" />
                                    {{ $site->isPublished() ? 'Unpublish' : 'Publish' }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <div class="card table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Website</th><th>Status</th><th>Pages</th><th>Views</th><th>Updated</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($websites as $site)
                            <tr wire:key="row-{{ $site->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0">
                                            <x-icon name="globe" class="w-4 h-4 text-brand-500" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-medium truncate">{{ $site->name }}</p>
                                            <p class="text-xs text-tertiary truncate">{{ $site->display_domain }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td><x-ui.badge :color="$site->status->color()" dot>{{ $site->status->label() }}</x-ui.badge></td>
                                <td class="tabular-nums">{{ $site->pages_count }}</td>
                                <td class="tabular-nums">{{ number_format($site->views_count) }}</td>
                                <td class="text-tertiary text-xs whitespace-nowrap">{{ $site->updated_at->diffForHumans(short: true) }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('builder.edit', $site) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <a href="{{ route('site.preview', $site->subdomain) }}" target="_blank" class="btn btn-ghost btn-icon"><x-icon name="external" class="w-4 h-4" /></a>
                                        <button wire:click="$set('confirmingDelete', {{ $site->id }})" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($websites->hasPages())
            <div class="mt-6">{{ $websites->links() }}</div>
        @endif
    </div>

    {{-- create modal --}}
    <x-ui.modal show="createOpen" title="Create a new website" max-width="max-w-lg">
        <form wire:submit="create" class="space-y-4">
            <div>
                <label class="label">Website name</label>
                <input wire:model="name" type="text" class="field @error('name') field-error @enderror" placeholder="My awesome website" autofocus>
                @error('name')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Description <span class="text-tertiary font-normal">(optional)</span></label>
                <textarea wire:model="description" rows="2" class="field" placeholder="What is this site about?"></textarea>
            </div>
            <div>
                <label class="label">Category</label>
                <select wire:model="category" class="field">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rounded-xl bg-brand-500/8 ring-1 ring-brand-500/15 p-3.5 flex gap-3">
                <x-icon name="sparkles" class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" />
                <p class="text-xs text-secondary">
                    Prefer a head start? <a href="{{ route('app.ai') }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Use the AI generator</a>
                    to create a complete multi-page site from a single sentence.
                </p>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-ghost" x-on:click="createOpen = false">Cancel</button>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="create">Create website</span>
                    <span wire:loading wire:target="create">Creating…</span>
                </button>
            </div>
        </form>
    </x-ui.modal>

    {{-- delete confirm --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('confirmingDelete', null)"></div>
            <div class="relative card shadow-2xl max-w-md w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/12 grid place-items-center mb-4">
                    <x-icon name="trash" class="w-6 h-6 text-rose-500" />
                </div>
                <h3 class="text-lg font-semibold">Delete this website?</h3>
                <p class="text-sm text-secondary mt-1.5">
                    The site and all of its pages will be moved to trash. This can be undone by an administrator.
                </p>
                <div class="flex justify-end gap-2 mt-6">
                    <button class="btn btn-ghost" wire:click="$set('confirmingDelete', null)">Cancel</button>
                    <button class="btn btn-danger" wire:click="delete({{ $confirmingDelete }})">Delete website</button>
                </div>
            </div>
        </div>
    @endif
</div>
