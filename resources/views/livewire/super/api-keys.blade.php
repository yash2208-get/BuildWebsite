<div class="space-y-6">
    <x-ui.page-header title="API Key Management" description="Issue and revoke platform API credentials.">
        <button wire:click="$set('showCreate', true)" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Issue key</button>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Total keys" :value="$totals['all']" icon="key" color="brand" />
        <x-ui.stat label="Active" :value="$totals['active']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Requests" :value="number_format($totals['requests'])" icon="activity" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search keys…">
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No API keys issued" emptyIcon="key" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Key</x-ui.th>
            <x-ui.th>Owner</x-ui.th>
            <x-ui.th>Rate limit</x-ui.th>
            <x-ui.th sort="usage_count" :sortIcon="$this->sortIcon('usage_count')">Requests</x-ui.th>
            <x-ui.th>Last used</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary font-mono">{{ $row->masked }}</p>
                        </div></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="text-xs tabular-nums">{{ $row->rate_limit }}/min</span></td>
                    <td><span class="tabular-nums">{{ number_format($row->usage_count) }}</span></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->last_used_at?->diffForHumans(short: true) ?? 'Never' }}</span></td>
                    <td class="text-right"><button wire:click="revoke({{ $row->id }})" wire:confirm="Revoke this API key?" class="btn btn-ghost btn-sm text-rose-500">Revoke</button></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

    @if($plainKey)
        <div class="rounded-2xl bg-emerald-500/10 ring-1 ring-emerald-500/25 p-5">
            <div class="flex items-start gap-3">
                <x-icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm">New API key generated</p>
                    <p class="text-xs text-secondary mt-1">Copy it now — it will never be shown again.</p>
                    <div class="flex items-center gap-2 mt-3" x-data="copyable(@js($plainKey))">
                        <code class="flex-1 px-3 py-2 rounded-lg bg-surface border border-subtle font-mono text-xs truncate">{{ $plainKey }}</code>
                        <button x-on:click="copy()" class="btn btn-secondary btn-sm shrink-0">
                            <x-icon name="copy" class="w-3.5 h-3.5" /> <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>
                <button wire:click="$set('plainKey', null)" class="btn btn-ghost btn-icon shrink-0"><x-icon name="x" class="w-4 h-4" /></button>
            </div>
        </div>
    @endif

    @if($showCreate)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('showCreate', false)"></div>
            <div class="relative card shadow-2xl max-w-lg w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <h3 class="text-lg font-semibold">Issue a new API key</h3>
                <form wire:submit="createKey" class="space-y-4 mt-5">
                    <div>
                        <label class="label">Key name</label>
                        <input wire:model="keyName" type="text" class="field @error('keyName') field-error @enderror" placeholder="Production integration">
                        @error('keyName')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Owner</label>
                            <select wire:model="ownerId" class="field">
                                <option value="">Platform (no owner)</option>
                                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Rate limit (per minute)</label>
                            <input wire:model="rateLimit" type="number" min="10" class="field">
                        </div>
                    </div>
                    <div>
                        <label class="label">Abilities</label>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($allAbilities as $key => $label)
                                <button type="button" wire:click="toggleAbility('{{ $key }}')"
                                        @class(['px-3 py-2 rounded-lg text-xs font-medium text-left transition-all border',
                                                'bg-brand-500/12 border-brand-500/30 text-brand-600 dark:text-brand-300' => in_array($key, $abilities),
                                                'border-subtle text-tertiary hover:text-secondary' => !in_array($key, $abilities)])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="label">Expires on <span class="text-tertiary font-normal">(optional)</span></label>
                        <input wire:model="expiresAt" type="date" class="field">
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showCreate', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate key</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
