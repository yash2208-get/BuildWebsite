<div class="space-y-6">
    <x-ui.page-header title="Subscriptions" description="Active and historical plan subscriptions.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Cancelled" :value="$totals['cancelled']" icon="x-circle" color="rose" />
        <x-ui.stat label="Monthly value" :value="'$'.number_format($totals['mrr'], 2)" icon="dollar" color="brand" />
    </div>

    <x-ui.toolbar placeholder="Search by customer…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="trialing">Trialing</option>
                <option value="active">Active</option>
                <option value="past_due">Past due</option>
                <option value="canceled">Canceled</option>
                <option value="expired">Expired</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No subscriptions found" emptyIcon="credit-card" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Plan</x-ui.th>
            <x-ui.th>Cycle</x-ui.th>
            <x-ui.th sort="amount" :sortIcon="$this->sortIcon('amount')">Amount</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th>Renews</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><x-ui.badge color="brand">{{ $row->plan?->name }}</x-ui.badge></td>
                    <td><span class="text-xs capitalize">{{ $row->billing_cycle }}</span></td>
                    <td><span class="font-semibold tabular-nums">${{ number_format((float) $row->amount, 2) }}</span></td>
                    <td><x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->current_period_end?->format('M j, Y') ?? '—' }}</span></td>
                    <td class="text-right">@if($row->status->isUsable())<button wire:click="cancelSubscription({{ $row->id }})" wire:confirm="Cancel this subscription?" class="btn btn-ghost btn-sm text-rose-500">Cancel</button>@endif</td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
