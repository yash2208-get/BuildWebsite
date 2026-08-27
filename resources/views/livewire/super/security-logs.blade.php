<div class="space-y-6">
    <x-ui.page-header title="Security Logs" description="Authentication events and security-relevant actions.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Total events" :value="number_format($totals['all'])" icon="shield" color="brand" />
        <x-ui.stat label="Warnings" :value="$totals['warnings']" icon="alert" color="amber" />
        <x-ui.stat label="Today" :value="$totals['today']" icon="clock" color="sky" />
    </div>

    <x-ui.toolbar placeholder="Search events…">
        <select wire:model.live="filters.level" class="field lg:w-44 shrink-0">
            <option value="">All levels</option>
                <option value="info">Info</option>
                <option value="success">Success</option>
                <option value="warning">Warning</option>
                <option value="critical">Critical</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No security events recorded" emptyIcon="shield" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="event" :sortIcon="$this->sortIcon('event')">Event</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>IP address</x-ui.th>
            <x-ui.th>User agent</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">When</x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><x-ui.badge :color="match($row->level) { 'warning' => 'amber', 'critical' => 'rose', 'success' => 'emerald', default => 'slate' }">{{ Str::headline($row->event) }}</x-ui.badge></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="font-mono text-xs">{{ $row->ip_address ?? '—' }}</span></td>
                    <td><span class="text-xs text-tertiary">{{ Str::limit($row->user_agent ?? '—', 40) }}</span></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
