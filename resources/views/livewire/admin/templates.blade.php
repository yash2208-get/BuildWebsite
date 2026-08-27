<div class="space-y-6">
    <x-ui.page-header title="Templates" description="Curate the template gallery.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Total" :value="$totals['all']" icon="layout" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Premium" :value="$totals['premium']" icon="crown" color="amber" />
    </div>

    <x-ui.toolbar placeholder="Search templates…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No templates found" emptyIcon="layout" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Template</x-ui.th>
            <x-ui.th sort="category" :sortIcon="$this->sortIcon('category')">Category</x-ui.th>
            <x-ui.th sort="uses_count" :sortIcon="$this->sortIcon('uses_count')">Uses</x-ui.th>
            <x-ui.th>Tier</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->description, 54) }}</p>
                        </div></td>
                    <td><x-ui.badge color="slate">{{ ucfirst($row->category) }}</x-ui.badge></td>
                    <td><span class="tabular-nums">{{ number_format($row->uses_count ?? 0) }}</span></td>
                    <td>@if($row->is_premium)<x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /> Pro</x-ui.badge>@else<x-ui.badge color="emerald">Free</x-ui.badge>@endif</td>
                    <td><x-ui.badge :color="$row->is_active ? 'emerald' : 'slate'" dot>{{ $row->is_active ? 'Visible' : 'Hidden' }}</x-ui.badge></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleFeatured({{ $row->id }})" class="btn btn-ghost btn-icon" title="Feature"><x-icon name="star" class="w-4 h-4 {{ $row->is_featured ? 'fill-amber-400 text-amber-400' : '' }}" /></button>
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle visibility"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
