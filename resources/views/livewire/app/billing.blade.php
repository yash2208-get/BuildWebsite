<div class="space-y-6">
    <x-ui.page-header title="Billing & Plans" description="Manage your subscription, usage and invoices." />

    {{-- current plan --}}
    <div class="rounded-2xl p-6 bg-gradient-to-br from-brand-500/10 via-violet-500/8 to-cyan-400/8 ring-1 ring-brand-500/15">
        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-2xl font-bold">{{ $currentPlan?->name ?? 'Free' }} plan</h2>
                    @if($subscription?->status === 'active')
                        <x-ui.badge color="emerald" dot>Active</x-ui.badge>
                    @elseif($subscription?->cancelled_at)
                        <x-ui.badge color="amber">Cancels {{ $subscription->ends_at?->format('M j, Y') }}</x-ui.badge>
                    @endif
                    @if($subscription?->onTrial())
                        <x-ui.badge color="violet">Trial ends {{ $subscription->trial_ends_at->diffForHumans() }}</x-ui.badge>
                    @endif
                </div>
                <p class="text-sm text-secondary mt-2">{{ $currentPlan?->description ?? 'Get started with the essentials, free forever.' }}</p>
                @if($subscription)
                    <p class="text-xs text-tertiary mt-2">
                        ${{ number_format((float) $subscription->amount, 2) }} / {{ $subscription->billing_cycle === 'yearly' ? 'year' : 'month' }}
                        · Renews {{ $subscription->current_period_end?->format('M j, Y') }}
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($subscription && !$subscription->cancelled_at)
                    <button wire:click="$set('confirmingCancel', true)" class="btn btn-ghost">Cancel plan</button>
                @elseif($subscription?->cancelled_at)
                    <button wire:click="resumeSubscription" class="btn btn-secondary">Resume plan</button>
                @endif
                <a href="#plans" class="btn btn-primary"><x-icon name="zap" class="w-4 h-4" /> Change plan</a>
            </div>
        </div>

        {{-- usage --}}
        <div class="grid gap-4 sm:grid-cols-3 mt-6 pt-6 border-t border-brand-500/15">
            @foreach([
                ['Websites', $usage['websites'], 'globe'],
                ['AI credits', $usage['ai_credits'], 'sparkles'],
                ['Storage (MB)', $usage['storage'], 'database'],
            ] as [$label, $data, $icon])
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex items-center gap-1.5 text-xs font-medium text-secondary">
                            <x-icon :name="$icon" class="w-3.5 h-3.5" /> {{ $label }}
                        </span>
                        <span class="text-xs tabular-nums text-tertiary">
                            {{ number_format($data['used'], $label === 'Storage (MB)' ? 1 : 0) }}
                            @if($data['limit'] >= 0) / {{ number_format($data['limit']) }} @else / ∞ @endif
                        </span>
                    </div>
                    @if($data['limit'] >= 0)
                        <x-ui.progress :value="$data['used']" :max="$data['limit']" />
                    @else
                        <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500/50 to-cyan-400/50"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- coupon --}}
    <x-ui.card title="Have a coupon?" padding="p-5">
        @if($appliedCoupon)
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/20">
                <x-icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold">{{ $appliedCoupon['code'] }} applied</p>
                    <p class="text-xs text-secondary">{{ $appliedCoupon['label'] }} off your next payment</p>
                </div>
                <button wire:click="removeCoupon" class="btn btn-ghost btn-sm shrink-0">Remove</button>
            </div>
        @else
            <form wire:submit="applyCoupon" class="flex gap-2">
                <input wire:model="couponCode" type="text" class="field flex-1 uppercase font-mono @error('couponCode') field-error @enderror" placeholder="ENTER CODE">
                <button type="submit" class="btn btn-secondary shrink-0">Apply</button>
            </form>
            @error('couponCode')<p class="error-text">{{ $message }}</p>@enderror
        @endif
    </x-ui.card>

    {{-- plans --}}
    <div id="plans" x-data="{ cycle: @entangle('cycle') }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <h2 class="text-lg font-bold">Available plans</h2>
            <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-surface-muted border border-subtle">
                <button x-on:click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-surface shadow-sm' : 'text-tertiary'" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all">Monthly</button>
                <button x-on:click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-surface shadow-sm' : 'text-tertiary'" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all flex items-center gap-2">
                    Yearly <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">−20%</span>
                </button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-4 items-start">
            @foreach($plans as $plan)
                @php $isCurrent = $currentPlan?->id === $plan->id; @endphp
                <div @class(['card p-5 relative flex flex-col h-full',
                             'ring-2 ring-brand-500' => $isCurrent,
                             'card-hover' => !$isCurrent])>
                    @if($isCurrent)
                        <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full bg-brand-500 text-white text-[10px] font-bold uppercase tracking-wider">Current</span>
                    @elseif($plan->is_featured)
                        <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full bg-gradient-to-r from-violet-500 to-brand-500 text-white text-[10px] font-bold uppercase tracking-wider">Popular</span>
                    @endif

                    <h3 class="font-bold">{{ $plan->name }}</h3>
                    <div class="mt-3 flex items-baseline gap-1">
                        <span class="text-3xl font-extrabold tabular-nums"
                              x-text="cycle === 'yearly' ? '${{ number_format($plan->yearly_price / 12, 0) }}' : '${{ number_format($plan->monthly_price, 0) }}'"></span>
                        <span class="text-xs text-tertiary">/mo</span>
                    </div>

                    <button wire:click="$set('confirmingPlan', {{ $plan->id }})" @disabled($isCurrent)
                            @class(['btn btn-sm w-full mt-4',
                                    'btn-secondary opacity-50 cursor-not-allowed' => $isCurrent,
                                    'btn-primary' => !$isCurrent && $plan->is_featured,
                                    'btn-secondary' => !$isCurrent && !$plan->is_featured])>
                        {{ $isCurrent ? 'Current plan' : (($plan->monthly_price > ($currentPlan?->monthly_price ?? 0)) ? 'Upgrade' : 'Switch') }}
                    </button>

                    <ul class="space-y-2 mt-5 pt-4 border-t border-subtle flex-1">
                        @foreach(array_slice($plan->features ?? [], 0, 6) as $feature)
                            <li class="flex items-start gap-2 text-xs">
                                <x-icon name="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                                <span class="text-secondary leading-snug">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    {{-- invoices --}}
    <x-ui.card title="Billing history" padding="p-0">
        @if($invoices->isEmpty())
            <x-ui.empty-state compact icon="receipt" title="No invoices yet" description="Invoices appear here after your first payment." />
        @else
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr><th>Invoice</th><th>Date</th><th>Amount</th><th>Status</th><th class="text-right">Receipt</th></tr></thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td class="font-mono text-xs">{{ $invoice->invoice_number }}</td>
                                <td class="text-secondary">{{ $invoice->created_at->format('M j, Y') }}</td>
                                <td class="font-semibold tabular-nums">${{ number_format((float) $invoice->total, 2) }}</td>
                                <td>
                                    <x-ui.badge :color="match($invoice->status) { 'paid' => 'emerald', 'pending' => 'amber', 'refunded' => 'slate', default => 'rose' }">
                                        {{ ucfirst($invoice->status) }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-ghost btn-sm"><x-icon name="download" class="w-3.5 h-3.5" /> PDF</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>

    {{-- confirm plan change --}}
    @if($confirmingPlan)
        @php $target = $plans->firstWhere('id', $confirmingPlan); @endphp
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('confirmingPlan', null)"></div>
            <div class="relative card shadow-2xl max-w-md w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <div class="w-12 h-12 rounded-2xl bg-brand-500/12 grid place-items-center mb-4">
                    <x-icon name="zap" class="w-6 h-6 text-brand-500" />
                </div>
                <h3 class="text-lg font-semibold">Switch to {{ $target?->name }}?</h3>
                <p class="text-sm text-secondary mt-2">
                    You will be charged
                    <strong class="text-primary">${{ number_format((float) ($cycle === 'yearly' ? $target?->yearly_price : $target?->monthly_price), 2) }}</strong>
                    {{ $cycle === 'yearly' ? 'per year' : 'per month' }}.
                    @if($appliedCoupon) Your coupon <strong>{{ $appliedCoupon['code'] }}</strong> will be applied. @endif
                </p>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="$set('confirmingPlan', null)" class="btn btn-ghost">Cancel</button>
                    <button wire:click="subscribe({{ $confirmingPlan }})" class="btn btn-primary">Confirm & pay</button>
                </div>
            </div>
        </div>
    @endif

    {{-- confirm cancel --}}
    @if($confirmingCancel)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('confirmingCancel', false)"></div>
            <div class="relative card shadow-2xl max-w-md w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/12 grid place-items-center mb-4">
                    <x-icon name="alert" class="w-6 h-6 text-amber-500" />
                </div>
                <h3 class="text-lg font-semibold">Cancel your subscription?</h3>
                <p class="text-sm text-secondary mt-2">
                    You will keep full access until {{ $subscription?->current_period_end?->format('F j, Y') }},
                    then move to the Free plan. You can resume at any time before then.
                </p>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="$set('confirmingCancel', false)" class="btn btn-ghost">Keep my plan</button>
                    <button wire:click="cancelSubscription" class="btn btn-danger">Cancel subscription</button>
                </div>
            </div>
        </div>
    @endif
</div>
