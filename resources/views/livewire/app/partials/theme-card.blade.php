@php $colors = $theme->colors ?? []; @endphp
<div wire:key="theme-{{ $theme->id }}" class="card card-hover overflow-hidden group">
    <div class="h-28 relative" style="background: {{ $colors['background'] ?? '#ffffff' }}">
        <div class="absolute inset-0 flex items-center justify-center gap-2">
            @foreach(['primary','secondary','accent'] as $role)
                <span class="w-10 h-10 rounded-xl shadow-sm ring-1 ring-black/5" style="background: {{ $colors[$role] ?? '#6366f1' }}"></span>
            @endforeach
        </div>
        @if($theme->is_default)
            <span class="absolute top-3 right-3"><x-ui.badge color="emerald">Default</x-ui.badge></span>
        @endif
    </div>
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h3 class="font-semibold text-sm truncate">{{ $theme->name }}</h3>
                <p class="text-xs text-tertiary truncate mt-0.5">
                    {{ $theme->typography['heading_font'] ?? 'Inter' }} · {{ $theme->typography['body_font'] ?? 'Inter' }}
                </p>
            </div>
            @if($owned)
                <button wire:click="deleteTheme({{ $theme->id }})" class="btn btn-ghost btn-icon text-rose-500 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            @endif
        </div>
        <button wire:click="$set('applyingTo', {{ $theme->id }})" class="btn btn-secondary btn-sm w-full mt-3">
            <x-icon name="check" class="w-3.5 h-3.5" /> Apply to website
        </button>
    </div>
</div>
