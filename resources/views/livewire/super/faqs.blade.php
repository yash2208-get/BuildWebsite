<div class="space-y-6">
    <x-ui.page-header title="FAQ Management" description="Questions shown on the public help pages.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Total" :value="$totals['all']" icon="help" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
    </div>

    <x-ui.toolbar placeholder="Search questions…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No FAQs found" emptyIcon="help" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="question" :sortIcon="$this->sortIcon('question')">Question</x-ui.th>
            <x-ui.th sort="category" :sortIcon="$this->sortIcon('category')">Category</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->question }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->answer, 70) }}</p>
                        </div></td>
                    <td><x-ui.badge color="slate">{{ ucfirst($row->category ?: 'general') }}</x-ui.badge></td>
                    <td><x-ui.badge :color="$row->is_active ? 'emerald' : 'slate'" dot>{{ $row->is_active ? 'Visible' : 'Hidden' }}</x-ui.badge></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteFaq({{ $row->id }})" wire:confirm="Delete this FAQ?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
