<div class="space-y-6">
    <x-ui.page-header title="Revenue Analytics" description="Subscription economics, growth and customer value.">
        <select wire:model.live="months" class="field w-auto">
            @foreach([6 => 'Last 6 months', 12 => 'Last 12 months', 24 => 'Last 24 months'] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </select>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Lifetime revenue" :value="'$'.number_format($metrics['total'], 2)" icon="dollar" color="emerald" />
        <x-ui.stat label="This month" :value="'$'.number_format($metrics['thisMonth'], 2)" icon="trend-up" color="brand" :trend="$metrics['growth']" />
        <x-ui.stat label="MRR" :value="'$'.number_format($metrics['mrr'], 2)" icon="repeat" color="violet" />
        <x-ui.stat label="ARR" :value="'$'.number_format($metrics['arr'], 2)" icon="chart" color="sky" />
    </div>

    <x-ui.card title="Monthly recurring revenue" :subtitle="'Paid invoices across the last '.$months.' months'">
        <div class="h-80" wire:ignore
             x-data="chart({
                type: 'bar', labels: @js($labels),
                datasets: [{ label: 'Revenue', data: @js($series), backgroundColor: 'rgba(16,185,129,.8)',
                             hoverBackgroundColor: '#10b981', borderRadius: 6, maxBarThickness: 40 }],
                money: true
             })">
            <canvas x-ref="canvas"></canvas>
        </div>
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="ARPU" :value="'$'.number_format($metrics['arpu'], 2)" icon="user-check" color="brand" />
        <x-ui.stat label="Est. LTV" :value="'$'.number_format($metrics['ltv'], 2)" icon="crown" color="amber" />
        <x-ui.stat label="Churn rate" :value="$metrics['churn'].'%'" icon="trend-down" color="rose" />
        <x-ui.stat label="Active subs" :value="number_format($metrics['activeSubs'])" icon="credit-card" color="emerald" />
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.card padding="p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-tertiary">Pending</p>
            <p class="text-2xl font-bold tabular-nums mt-2 text-amber-500">${{ number_format($metrics['pending'], 2) }}</p>
            <p class="text-xs text-tertiary mt-1">Awaiting payment</p>
        </x-ui.card>
        <x-ui.card padding="p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-tertiary">Refunded</p>
            <p class="text-2xl font-bold tabular-nums mt-2 text-rose-500">${{ number_format($metrics['refunded'], 2) }}</p>
            <p class="text-xs text-tertiary mt-1">Returned to customers</p>
        </x-ui.card>
        <x-ui.card padding="p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-tertiary">Average invoice</p>
            <p class="text-2xl font-bold tabular-nums mt-2">${{ number_format($metrics['avgInvoice'], 2) }}</p>
            <p class="text-xs text-tertiary mt-1">Across all paid invoices</p>
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Revenue by plan" padding="p-0">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th class="text-right">Subscribers</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $maxRev = max(1, (float) $byPlan->max('revenue')); @endphp
                        @foreach($byPlan as $p)
                            <tr>
                                <td>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ $p->name }}</p>
                                        <div class="h-1.5 rounded-full bg-surface-muted overflow-hidden mt-1.5 max-w-[10rem]">
                                            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-violet-500"
                                                 style="width: {{ round(((float) $p->revenue / $maxRev) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right tabular-nums">{{ $p->subscribers }}</td>
                                <td class="text-right font-semibold tabular-nums">${{ number_format((float) $p->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card title="Top customers" subtitle="By lifetime spend" padding="p-0">
            @forelse($topCustomers as $c)
                <div class="flex items-center gap-3 px-5 py-3 border-b border-subtle last:border-0">
                    <x-ui.avatar :name="$c->user?->name ?? '—'" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ $c->user?->name ?? 'Deleted user' }}</p>
                        <p class="text-xs text-tertiary">{{ $c->invoices }} {{ Str::plural('invoice', $c->invoices) }}</p>
                    </div>
                    <span class="text-sm font-semibold tabular-nums shrink-0">${{ number_format((float) $c->spent, 2) }}</span>
                </div>
            @empty
                <x-ui.empty-state compact icon="users" title="No paying customers yet" />
            @endforelse
        </x-ui.card>
    </div>

    <x-ui.card title="Latest invoices" padding="p-0">
        <x-slot:actions><a href="{{ route('super.invoices') }}" class="btn btn-ghost btn-sm">View all</a></x-slot:actions>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>Invoice</th><th>Customer</th><th>Plan</th><th class="text-right">Total</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($recentInvoices as $inv)
                        <tr>
                            <td><span class="font-mono text-xs">{{ $inv->invoice_number }}</span></td>
                            <td><x-ui.user-cell :user="$inv->user" /></td>
                            <td><span class="text-xs text-tertiary">{{ $inv->subscription?->plan?->name ?? '—' }}</span></td>
                            <td class="text-right font-semibold tabular-nums">${{ number_format((float) $inv->total, 2) }}</td>
                            <td>
                                <x-ui.badge :color="match($inv->status) { 'paid' => 'emerald', 'pending' => 'amber', 'refunded' => 'slate', default => 'rose' }">
                                    {{ ucfirst($inv->status) }}
                                </x-ui.badge>
                            </td>
                            <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $inv->created_at->format('M j, Y') }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
