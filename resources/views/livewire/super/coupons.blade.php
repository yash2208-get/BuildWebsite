<div class="space-y-6">
    <x-ui.page-header title="Coupons" description="Discount codes and promotional offers.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Total" :value="$totals['all']" icon="tag" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Redemptions" :value="number_format($totals['redemptions'])" icon="download" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search coupons…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No coupons found" emptyIcon="tag" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="code" :sortIcon="$this->sortIcon('code')">Code</x-ui.th>
            <x-ui.th>Discount</x-ui.th>
            <x-ui.th>Used</x-ui.th>
            <x-ui.th>Expires</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><code class="font-mono text-sm font-semibold">{{ $row->code }}</code></td>
                    <td><x-ui.badge color="violet">{{ $row->display_value }}</x-ui.badge></td>
                    <td><span class="tabular-nums text-sm">{{ $row->redemptions }}@if($row->max_redemptions) / {{ $row->max_redemptions }}@endif</span></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->expires_at?->format('M j, Y') ?? 'Never' }}</span></td>
                    <td><x-ui.badge :color="$row->isRedeemable() ? 'emerald' : 'slate'" dot>{{ $row->isRedeemable() ? 'Active' : 'Inactive' }}</x-ui.badge></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteCoupon({{ $row->id }})" wire:confirm="Delete this coupon?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
