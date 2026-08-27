<div class="space-y-6">
    <x-ui.page-header title="Admin Management" description="Staff accounts with elevated platform access.">
        <button wire:click="$set('showCreate', true)" class="btn btn-primary">
            <x-icon name="plus" class="w-4 h-4" /> Add admin
        </button>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Staff accounts" :value="$totals['all']" icon="shield" color="brand" />
        <x-ui.stat label="Super admins" :value="$totals['super']" icon="crown" color="violet" />
        <x-ui.stat label="Admins" :value="$totals['admin']" icon="user-check" color="sky" />
    </div>

    <x-ui.toolbar placeholder="Search staff by name or email…">
        <select wire:model.live="filters.role" class="field lg:w-48 shrink-0">
            <option value="">All roles</option>
            @foreach($roles as $r)
                <option value="{{ $r->name }}">{{ Str::headline($r->name) }}</option>
            @endforeach
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No staff accounts" emptyIcon="shield" emptyDescription="Invite an administrator to help manage the platform.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">Administrator</x-ui.th>
            <x-ui.th>Role</x-ui.th>
            <x-ui.th>Permissions</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="last_login_at" :sortIcon="$this->sortIcon('last_login_at')">Last seen</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="a-{{ $row->id }}">
                    <td><x-ui.user-cell :user="$row" /></td>
                    <td>
                        @foreach($row->roles as $r)
                            <x-ui.badge :color="$r->name === 'super-admin' ? 'violet' : 'sky'">
                                <x-icon :name="$r->name === 'super-admin' ? 'crown' : 'shield'" class="w-3 h-3" />
                                {{ Str::headline($r->name) }}
                            </x-ui.badge>
                        @endforeach
                    </td>
                    <td><span class="text-xs text-tertiary tabular-nums">{{ $row->getAllPermissions()->count() }} granted</span></td>
                    <td><x-ui.badge :color="$row->status === 'active' ? 'emerald' : 'rose'" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->last_login_at?->diffForHumans(short: true) ?? 'Never' }}</span></td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            @unless($row->id === auth()->id())
                                <button wire:click="toggleStatus({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle status">
                                    <x-icon name="{{ $row->status === 'active' ? 'ban' : 'check-circle' }}" class="w-4 h-4" />
                                </button>
                                <button wire:click="revokeAccess({{ $row->id }})" wire:confirm="Remove staff access for this user?" class="btn btn-ghost btn-icon text-rose-500" title="Revoke access">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            @else
                                <span class="text-xs text-tertiary italic pr-2">You</span>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

    @if($showCreate)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('showCreate', false)"></div>
            <div class="relative card shadow-2xl max-w-lg w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <h3 class="text-lg font-semibold">Add an administrator</h3>
                <p class="text-sm text-tertiary mt-1">They will receive an email with sign-in instructions.</p>

                <form wire:submit="createAdmin" class="space-y-4 mt-5">
                    <div>
                        <label class="label">Full name</label>
                        <input wire:model="name" type="text" class="field @error('name') field-error @enderror" placeholder="Jamie Rivera">
                        @error('name')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Email address</label>
                        <input wire:model="email" type="email" class="field @error('email') field-error @enderror" placeholder="jamie@company.com">
                        @error('email')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Temporary password</label>
                        <input wire:model="password" type="text" class="field @error('password') field-error @enderror" placeholder="At least 8 characters">
                        @error('password')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Role</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $r)
                                <button type="button" wire:click="$set('role', '{{ $r->name }}')"
                                        @class(['px-4 py-3 rounded-xl text-left transition-all border',
                                                'bg-brand-500/10 border-brand-500/35 ring-1 ring-brand-500/20' => $role === $r->name,
                                                'border-subtle hover:border-brand-500/25' => $role !== $r->name])>
                                    <span class="flex items-center gap-2 text-sm font-medium">
                                        <x-icon :name="$r->name === 'super-admin' ? 'crown' : 'shield'" class="w-4 h-4 text-brand-500" />
                                        {{ Str::headline($r->name) }}
                                    </span>
                                    <span class="block text-xs text-tertiary mt-1">
                                        {{ $r->name === 'super-admin' ? 'Unrestricted platform control' : 'Content & customer management' }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        @error('role')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showCreate', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create administrator</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
