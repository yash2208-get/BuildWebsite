<div class="space-y-6">
    <x-ui.page-header title="AI Generation Logs" description="Every request sent through the AI engine.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total" :value="number_format($totals['all'])" icon="sparkles" color="brand" />
        <x-ui.stat label="Completed" :value="number_format($totals['completed'])" icon="check-circle" color="emerald" />
        <x-ui.stat label="Failed" :value="$totals['failed']" icon="x-circle" color="rose" />
        <x-ui.stat label="Credits used" :value="number_format($totals['credits'])" icon="zap" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search prompts…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No generations recorded" emptyIcon="sparkles" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="type" :sortIcon="$this->sortIcon('type')">Type</x-ui.th>
            <x-ui.th>Prompt</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th sort="credits_used" :sortIcon="$this->sortIcon('credits_used')">Credits</x-ui.th>
            <x-ui.th sort="duration_ms" :sortIcon="$this->sortIcon('duration_ms')">Duration</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">When</x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><x-ui.badge color="violet">{{ $row->type->label() }}</x-ui.badge></td>
                    <td><span class="text-sm text-secondary">{{ Str::limit($row->prompt, 60) }}</span></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="tabular-nums">{{ $row->credits_used }}</span></td>
                    <td><span class="tabular-nums text-xs text-tertiary">{{ $row->duration_ms }}ms</span></td>
                    <td><x-ui.badge :color="$row->status === 'completed' ? 'emerald' : ($row->status === 'failed' ? 'rose' : 'amber')" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
