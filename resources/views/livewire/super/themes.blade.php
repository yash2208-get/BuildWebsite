<div class="space-y-6">
    <x-ui.page-header title="Theme Management" description="System and user-created themes.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Total" :value="$totals['all']" icon="palette" color="brand" />
        <x-ui.stat label="System" :value="$totals['system']" icon="shield" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search themes…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No themes found" emptyIcon="palette" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Theme</x-ui.th>
            <x-ui.th>Typography</x-ui.th>
            <x-ui.th>Scope</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="flex items-center gap-3 min-w-0">
                            <span class="flex gap-1 shrink-0">
                                @foreach(['primary','secondary','accent'] as $role)
                                    <span class="w-5 h-5 rounded-md ring-1 ring-black/8 dark:ring-white/10" style="background: {{ $row->colors[$role] ?? '#6366f1' }}"></span>
                                @endforeach
                            </span>
                            <p class="font-medium truncate">{{ $row->name }}</p>
                        </div></td>
                    <td><span class="text-xs text-tertiary">{{ $row->typography['heading_font'] ?? 'Inter' }}</span></td>
                    <td><x-ui.badge :color="$row->user_id ? 'slate' : 'violet'">{{ $row->user_id ? 'User' : 'System' }}</x-ui.badge></td>
                    <td>@if($row->is_default)<x-ui.badge color="emerald">Default</x-ui.badge>@else<x-ui.badge :color="$row->is_active ? 'sky' : 'slate'">{{ $row->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>@endif</td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button wire:click="makeDefault({{ $row->id }})" class="btn btn-ghost btn-icon" title="Make default"><x-icon name="star" class="w-4 h-4 {{ $row->is_default ? 'fill-amber-400 text-amber-400' : '' }}" /></button>
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
