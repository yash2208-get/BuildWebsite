<div class="space-y-6">
    <x-ui.page-header title="Media Library" description="Upload and manage images, video and documents.">
        <label class="btn btn-primary cursor-pointer">
            <x-icon name="upload" class="w-4 h-4" /> Upload files
            <input type="file" wire:model="uploads" multiple class="hidden" accept="image/*,video/*,.pdf,.doc,.docx">
        </label>
    </x-ui.page-header>

    {{-- storage meter --}}
    <x-ui.card padding="p-5">
        <div class="flex items-center justify-between mb-2.5">
            <div class="flex items-center gap-2">
                <x-icon name="database" class="w-4 h-4 text-tertiary" />
                <span class="text-sm font-medium">Storage used</span>
            </div>
            <span class="text-sm tabular-nums text-secondary">
                {{ number_format($usage['used'], 1) }} MB
                @if($usage['limit'] >= 0) / {{ number_format($usage['limit']) }} MB @else / Unlimited @endif
            </span>
        </div>
        @if($usage['limit'] >= 0)
            <x-ui.progress :value="$usage['used']" :max="$usage['limit']" />
        @endif
    </x-ui.card>

    {{-- upload progress --}}
    <div wire:loading wire:target="uploads" class="card p-5 flex items-center gap-4">
        <svg class="w-5 h-5 animate-spin text-brand-500 shrink-0" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-medium">Uploading your files…</p>
            <p class="text-xs text-tertiary mt-0.5">Large files may take a moment.</p>
        </div>
    </div>
    @error('uploads.*')<p class="error-text">{{ $message }}</p>@enderror

    {{-- filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <x-icon name="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
            <input wire:model.live.debounce.300ms="search" type="search" class="field pl-10" placeholder="Search files…">
        </div>
        <div class="flex gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
            @foreach(['all' => 'All', 'image' => 'Images', 'video' => 'Video', 'document' => 'Docs'] as $key => $label)
                <button wire:click="$set('type','{{ $key }}')"
                        @class(['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap',
                                'bg-surface shadow-sm' => $type === $key, 'text-tertiary hover:text-secondary' => $type !== $key])>
                    {{ $label }} <span class="tabular-nums opacity-60">{{ $counts[$key] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <x-ui.bulk-bar :count="count($selected)" label="file">
        <button wire:click="deleteSelected" class="btn btn-danger btn-sm"><x-icon name="trash" class="w-3.5 h-3.5" /> Delete</button>
    </x-ui.bulk-bar>

    @if($media->isEmpty())
        <div class="card">
            <x-ui.empty-state icon="image" title="{{ $search ? 'No files match your search' : 'Your library is empty' }}"
                description="{{ $search ? 'Try a different keyword.' : 'Upload images, video and documents to use across your websites.' }}">
                <label class="btn btn-primary cursor-pointer">
                    <x-icon name="upload" class="w-4 h-4" /> Upload your first file
                    <input type="file" wire:model="uploads" multiple class="hidden">
                </label>
            </x-ui.empty-state>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($media as $file)
                <div wire:key="media-{{ $file->id }}" class="card overflow-hidden group relative">
                    <label class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 has-[:checked]:opacity-100 transition-opacity">
                        <input type="checkbox" wire:model.live="selected" value="{{ $file->id }}"
                               class="rounded border-white/50 bg-black/30 backdrop-blur text-brand-500 focus:ring-brand-500/30 w-4 h-4">
                    </label>

                    <div class="aspect-square bg-surface-muted grid place-items-center overflow-hidden">
                        @if($file->type === 'image')
                            <img src="{{ $file->url }}" alt="{{ $file->alt_text }}" loading="lazy"
                                 class="w-full h-full object-cover transition-transform group-hover:scale-105">
                        @else
                            <x-icon :name="$file->type === 'video' ? 'video' : 'file'" class="w-10 h-10 text-tertiary" />
                        @endif
                    </div>

                    <div class="p-3">
                        <p class="text-xs font-medium truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                        <p class="text-[10px] text-tertiary mt-0.5">{{ $file->human_size ?? round($file->size / 1024).' KB' }}</p>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 flex gap-1 p-2 bg-gradient-to-t from-ink-950/85 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <button x-data="copyable(@js($file->url))" x-on:click="copy()" class="flex-1 text-[10px] font-semibold py-1.5 rounded-md bg-white/15 text-white hover:bg-white/25 backdrop-blur transition-colors">
                            <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                        </button>
                        <button wire:click="startEditing({{ $file->id }})" class="p-1.5 rounded-md bg-white/15 text-white hover:bg-white/25 backdrop-blur transition-colors">
                            <x-icon name="edit" class="w-3 h-3" />
                        </button>
                        <button wire:click="delete({{ $file->id }})" class="p-1.5 rounded-md bg-rose-500/70 text-white hover:bg-rose-500 backdrop-blur transition-colors">
                            <x-icon name="trash" class="w-3 h-3" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @if($media->hasPages())<div class="mt-6">{{ $media->links() }}</div>@endif
    @endif

    @if($editing)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('editing', null)"></div>
            <div class="relative card shadow-2xl max-w-md w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <h3 class="font-semibold text-lg">Edit alt text</h3>
                <p class="text-sm text-secondary mt-1">Descriptive alt text improves accessibility and SEO.</p>
                <form wire:submit="saveAlt" class="mt-5 space-y-4">
                    <input wire:model="altText" type="text" class="field" placeholder="A woman working at a laptop in a bright office" autofocus>
                    @error('altText')<p class="error-text">{{ $message }}</p>@enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('editing', null)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
