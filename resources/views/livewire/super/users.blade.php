<div class="space-y-6">
    <x-ui.page-header title="All Users" description="Every account on the platform, staff included.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total" :value="$totals['all']" icon="users" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Suspended" :value="$totals['suspended']" icon="ban" color="rose" />
        <x-ui.stat label="Staff" :value="$totals['staff']" icon="shield" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search users…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No users found" emptyIcon="users" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">User</x-ui.th>
            <x-ui.th>Role</x-ui.th>
            <x-ui.th>Plan</x-ui.th>
            <x-ui.th sort="websites_count" :sortIcon="$this->sortIcon('websites_count')">Sites</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Joined</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><x-ui.user-cell :user="$row" /></td>
                    <td>@foreach($row->roles as $r)<x-ui.badge :color="$r->name === 'super-admin' ? 'fuchsia' : ($r->name === 'admin' ? 'sky' : 'slate')">{{ Str::headline($r->name) }}</x-ui.badge>@endforeach</td>
                    <td><x-ui.badge color="{{ $row->subscription ? 'brand' : 'slate' }}">{{ $row->subscription?->plan?->name ?? 'Free' }}</x-ui.badge></td>
                    <td><span class="tabular-nums">{{ $row->websites_count }}</span></td>
                    <td><x-ui.badge :color="$row->status === 'active' ? 'emerald' : 'rose'" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            @unless($row->isSuperAdmin())
                                <button wire:click="impersonate({{ $row->id }})" wire:confirm="Sign in as this user?" class="btn btn-ghost btn-icon" title="Impersonate"><x-icon name="user-check" class="w-4 h-4" /></button>
                            @endunless
                            <button wire:click="toggleStatus({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle status"><x-icon name="{{ $row->status === 'active' ? 'ban' : 'check-circle' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteUser({{ $row->id }})" wire:confirm="Permanently delete this user?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
