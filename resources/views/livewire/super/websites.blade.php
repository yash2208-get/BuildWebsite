<div class="space-y-6">
    <x-ui.page-header title="All Websites" description="Platform-wide website inventory.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total" :value="$totals['all']" icon="globe" color="brand" />
        <x-ui.stat label="Published" :value="$totals['published']" icon="rocket" color="emerald" />
        <x-ui.stat label="Custom domains" :value="$totals['domains']" icon="link" color="violet" />
        <x-ui.stat label="Total views" :value="number_format($totals['views'])" icon="eye" color="sky" />
    </div>

    <x-ui.toolbar placeholder="Search websites…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No websites found" emptyIcon="globe" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Website</x-ui.th>
            <x-ui.th>Owner</x-ui.th>
            <x-ui.th sort="pages_count" :sortIcon="$this->sortIcon('pages_count')">Pages</x-ui.th>
            <x-ui.th sort="views_count" :sortIcon="$this->sortIcon('views_count')">Views</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ $row->display_domain }}</p>
                        </div></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="tabular-nums">{{ $row->pages_count }}</span></td>
                    <td><span class="tabular-nums">{{ number_format($row->views_count) }}</span></td>
                    <td><x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <a href="{{ route('site.preview', $row->subdomain) }}" target="_blank" class="btn btn-ghost btn-icon"><x-icon name="external" class="w-4 h-4" /></a>
                            <button wire:click="togglePublish({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->isPublished() ? 'eye-off' : 'rocket' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteWebsite({{ $row->id }})" wire:confirm="Delete this website?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
