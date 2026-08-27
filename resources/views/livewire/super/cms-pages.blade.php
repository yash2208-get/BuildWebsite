<div class="space-y-6">
    <x-ui.page-header title="CMS Pages" description="Marketing and legal pages on the public site.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Total" :value="$totals['all']" icon="file" color="brand" />
        <x-ui.stat label="Published" :value="$totals['published']" icon="check-circle" color="emerald" />
    </div>

    <x-ui.toolbar placeholder="Search pages…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No CMS pages found" emptyIcon="file" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="title" :sortIcon="$this->sortIcon('title')">Page</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="updated_at" :sortIcon="$this->sortIcon('updated_at')">Updated</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->title }}</p>
                            <p class="text-xs text-tertiary font-mono truncate">/p/{{ $row->slug }}</p>
                        </div></td>
                    <td><x-ui.badge :color="$row->status === 'published' ? 'emerald' : 'amber'" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->updated_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <a href="{{ route('page.show', $row->slug) }}" target="_blank" class="btn btn-ghost btn-icon"><x-icon name="external" class="w-4 h-4" /></a>
                            <button wire:click="togglePublished({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->status === 'published' ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deletePage({{ $row->id }})" wire:confirm="Delete this page?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
