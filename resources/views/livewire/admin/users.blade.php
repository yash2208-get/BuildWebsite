<div class="space-y-6">
    <x-ui.page-header title="User Management" description="View, search and moderate customer accounts.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total users" :value="$totals['all']" icon="users" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Suspended" :value="$totals['suspended']" icon="ban" color="rose" />
        <x-ui.stat label="New (30d)" :value="$totals['new']" icon="user-plus" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search by name, email or username…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="pending">Pending</option>
        </select>
    </x-ui.toolbar>

    <x-ui.bulk-bar :count="count($selected)" label="user">
        <button wire:click="bulkActivate" class="btn btn-secondary btn-sm">Activate</button>
        <button wire:click="bulkSuspend" class="btn btn-danger btn-sm">Suspend</button>
    </x-ui.bulk-bar>

    <x-ui.table :rows="$rows" empty="No users found" emptyIcon="users" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <th class="w-10"><input type="checkbox" wire:model.live="selectAll" class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4"></th>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">User</x-ui.th>
            <x-ui.th>Plan</x-ui.th>
            <x-ui.th sort="websites_count" :sortIcon="$this->sortIcon('websites_count')">Websites</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Joined</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><input type="checkbox" wire:model.live="selected" value="{{ $row->id }}" class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4"></td>
                    <td><x-ui.user-cell :user="$row" /></td>
                    <td><x-ui.badge color="{{ $row->subscription ? 'brand' : 'slate' }}">{{ $row->subscription?->plan?->name ?? 'Free' }}</x-ui.badge></td>
                    <td><span class="tabular-nums">{{ $row->websites_count }}</span></td>
                    <td><x-ui.badge :color="$row->status === 'active' ? 'emerald' : 'rose'" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button wire:click="$set('viewing', {{ $row->id }})" class="btn btn-ghost btn-icon" title="View"><x-icon name="eye" class="w-4 h-4" /></button>
                            <button wire:click="toggleStatus({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle status">
                                <x-icon name="{{ $row->status === 'active' ? 'ban' : 'check-circle' }}" class="w-4 h-4 {{ $row->status === 'active' ? 'text-rose-500' : 'text-emerald-500' }}" />
                            </button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
