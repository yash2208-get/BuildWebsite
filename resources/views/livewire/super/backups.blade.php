<div class="space-y-6">
    <x-ui.page-header title="Backup & Restore" description="Database and file snapshots.">
        <button wire:click="createBackup" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create backup</button>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Backups" :value="$totals['all']" icon="database" color="brand" />
        <x-ui.stat label="Total size" :value="$totals['size'].' MB'" icon="archive" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search backups…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No backups yet" emptyIcon="database" emptyDescription="Create your first snapshot to protect your data.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Backup</x-ui.th>
            <x-ui.th sort="type" :sortIcon="$this->sortIcon('type')">Type</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Created</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate font-mono text-xs">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary">{{ $row->human_size }}</p>
                        </div></td>
                    <td><x-ui.badge :color="$row->type === 'manual' ? 'sky' : 'slate'">{{ ucfirst($row->type) }}</x-ui.badge></td>
                    <td><x-ui.badge :color="$row->statusColor()" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button class="btn btn-ghost btn-icon" title="Download"><x-icon name="download" class="w-4 h-4" /></button>
                            <button wire:click="deleteBackup({{ $row->id }})" wire:confirm="Delete this backup?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
