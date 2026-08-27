<div class="space-y-6">
    <x-ui.page-header title="Invoices" description="Every invoice issued by the platform.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Paid total" :value="'$'.number_format($totals['paid'], 2)" icon="dollar" color="emerald" />
        <x-ui.stat label="Pending" :value="$totals['pending']" icon="clock" color="amber" />
        <x-ui.stat label="Refunded" :value="$totals['refunded']" icon="undo" color="slate" />
        <x-ui.stat label="Invoices" :value="number_format($totals['count'])" icon="receipt" color="brand" />
    </div>

    <x-ui.toolbar placeholder="Search invoices…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
                <option value="failed">Failed</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No invoices found" emptyIcon="receipt" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="invoice_number" :sortIcon="$this->sortIcon('invoice_number')">Invoice</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Plan</x-ui.th>
            <x-ui.th sort="total" :sortIcon="$this->sortIcon('total')">Total</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Date</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><span class="font-mono text-xs">{{ $row->invoice_number }}</span></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="text-xs text-tertiary">{{ $row->subscription?->plan?->name ?? '—' }}</span></td>
                    <td><span class="font-semibold tabular-nums">${{ number_format((float) $row->total, 2) }}</span></td>
                    <td><x-ui.badge :color="match($row->status) { 'paid' => 'emerald', 'pending' => 'amber', 'refunded' => 'slate', default => 'rose' }">{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            @if($row->status !== 'paid')<button wire:click="markPaid({{ $row->id }})" class="btn btn-ghost btn-sm text-emerald-500">Mark paid</button>@endif
                            @if($row->status === 'paid')<button wire:click="refund({{ $row->id }})" wire:confirm="Refund this invoice?" class="btn btn-ghost btn-sm">Refund</button>@endif
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
