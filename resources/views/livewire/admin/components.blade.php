<div class="space-y-6">
    <x-ui.page-header title="Components" description="Manage the drag-and-drop block library.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Total blocks" :value="$totals['all']" icon="grid" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
    </div>

    <x-ui.toolbar placeholder="Search blocks…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No components found" emptyIcon="grid" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Component</x-ui.th>
            <x-ui.th sort="category" :sortIcon="$this->sortIcon('category')">Category</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-surface-muted grid place-items-center text-base shrink-0">{{ $row->icon ?: '▦' }}</span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->name }}</p>
                                <p class="text-xs text-tertiary truncate">{{ Str::limit($row->description, 48) }}</p>
                            </div>
                        </div></td>
                    <td><x-ui.badge color="slate">{{ ucfirst($row->category) }}</x-ui.badge></td>
                    <td><x-ui.badge :color="$row->is_active ? 'emerald' : 'slate'" dot>{{ $row->is_active ? 'Active' : 'Hidden' }}</x-ui.badge></td>
                    <td class="text-right"><button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
