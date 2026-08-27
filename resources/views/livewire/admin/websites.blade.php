<div class="space-y-6">
    <x-ui.page-header title="Website Management" description="Every website created on the platform.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total" :value="$totals['all']" icon="globe" color="brand" />
        <x-ui.stat label="Published" :value="$totals['published']" icon="rocket" color="emerald" />
        <x-ui.stat label="Drafts" :value="$totals['draft']" icon="edit" color="amber" />
        <x-ui.stat label="Total views" :value="number_format($totals['views'])" icon="eye" color="sky" />
    </div>

    <x-ui.toolbar placeholder="Search by name, subdomain or owner…">
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
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Created</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0">
                                <x-icon name="globe" class="w-4 h-4 text-brand-500" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->name }}</p>
                                <p class="text-xs text-tertiary truncate">{{ $row->display_domain }}</p>
                            </div>
                        </div></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="tabular-nums">{{ $row->pages_count }}</span></td>
                    <td><span class="tabular-nums">{{ number_format($row->views_count) }}</span></td>
                    <td><x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <a href="{{ route('site.preview', $row->subdomain) }}" target="_blank" class="btn btn-ghost btn-icon" title="Preview"><x-icon name="external" class="w-4 h-4" /></a>
                            <button wire:click="togglePublish({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle publish"><x-icon name="{{ $row->isPublished() ? 'eye-off' : 'rocket' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteWebsite({{ $row->id }})" wire:confirm="Delete this website?" class="btn btn-ghost btn-icon text-rose-500" title="Delete"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
