<div class="space-y-6">
    <x-ui.page-header title="Audit & Activity Logs" description="A complete, immutable audit trail.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Total entries" :value="number_format($totals['all'])" icon="activity" color="brand" />
        <x-ui.stat label="Today" :value="$totals['today']" icon="clock" color="sky" />
    </div>

    <x-ui.toolbar placeholder="Search activity…">
        <select wire:model.live="filters.event" class="field lg:w-44 shrink-0">
            <option value="">All events</option>
            @foreach($events as $opt)
                <option value="{{ $opt }}">{{ Str::headline($opt) }}</option>
            @endforeach
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No activity recorded" emptyIcon="activity" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="event" :sortIcon="$this->sortIcon('event')">Event</x-ui.th>
            <x-ui.th>Description</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>IP</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">When</x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><x-ui.badge :color="$row->eventColor()">{{ Str::headline($row->event) }}</x-ui.badge></td>
                    <td><span class="text-sm">{{ $row->description }}</span></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="font-mono text-xs text-tertiary">{{ $row->ip_address ?? '—' }}</span></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
