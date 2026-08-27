<div class="space-y-6">
    <x-ui.page-header title="Account Settings" description="Manage your profile, security and integrations." />

    <div class="flex gap-1 p-1 rounded-xl bg-surface-muted border border-subtle overflow-x-auto scrollbar-thin">
        @foreach(['profile' => ['Profile','user'], 'security' => ['Security','shield'], 'notifications' => ['Notifications','bell'], 'api' => ['API keys','key'], 'danger' => ['Danger zone','alert']] as $key => [$label, $icon])
            <button wire:click="$set('tab','{{ $key }}')"
                    @class(['flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all',
                            'bg-surface shadow-sm text-primary' => $tab === $key,
                            'text-tertiary hover:text-secondary' => $tab !== $key])>
                <x-icon :name="$icon" class="w-4 h-4" /> {{ $label }}
            </button>
        @endforeach
    </div>

    @if($tab === 'profile')
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-ui.card title="Profile details" subtitle="This information appears across the platform.">
                    <form wire:submit="saveProfile" class="space-y-4">
                        <div class="flex items-center gap-5 pb-5 border-b border-subtle">
                            @if($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="w-20 h-20 rounded-2xl object-cover ring-2 ring-brand-500/25" alt="Preview">
                            @else
                                <x-ui.avatar :name="$this->user()->name" :src="$this->user()->avatar_url" size="xl" />
                            @endif
                            <div>
                                <label class="btn btn-secondary btn-sm cursor-pointer">
                                    <x-icon name="upload" class="w-3.5 h-3.5" /> Change photo
                                    <input type="file" wire:model="avatar" class="hidden" accept="image/*">
                                </label>
                                <p class="text-xs text-tertiary mt-2">JPG, PNG or WebP. Max 2 MB.</p>
                                @error('avatar')<p class="error-text">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Full name</label>
                                <input wire:model="name" type="text" class="field @error('name') field-error @enderror">
                                @error('name')<p class="error-text">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="label">Username</label>
                                <input wire:model="username" type="text" class="field @error('username') field-error @enderror">
                                @error('username')<p class="error-text">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="label">Email address</label>
                            <input wire:model="email" type="email" class="field @error('email') field-error @enderror">
                            @error('email')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="label">Bio</label>
                            <textarea wire:model="bio" rows="3" class="field" placeholder="A short introduction…"></textarea>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Company</label>
                                <input wire:model="company" type="text" class="field" placeholder="Acme Inc.">
                            </div>
                            <div>
                                <label class="label">Website</label>
                                <input wire:model="website" type="url" class="field @error('website') field-error @enderror" placeholder="https://example.com">
                                @error('website')<p class="error-text">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="label">Timezone</label>
                            <select wire:model="timezone" class="field">
                                @foreach($timezones as $tz)<option value="{{ $tz }}">{{ str_replace('_', ' ', $tz) }}</option>@endforeach
                            </select>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save changes</button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <x-ui.card title="Account" padding="p-5" class="h-fit">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between"><span class="text-tertiary">Plan</span><x-ui.badge color="brand">{{ $this->user()->activePlan()?->name ?? 'Free' }}</x-ui.badge></div>
                    <div class="flex items-center justify-between"><span class="text-tertiary">Websites</span><span class="font-semibold tabular-nums">{{ $this->user()->websites()->count() }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-tertiary">Member since</span><span class="font-medium">{{ $this->user()->created_at->format('M Y') }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-tertiary">Last sign-in</span><span class="font-medium">{{ $this->user()->last_login_at?->diffForHumans() ?? '—' }}</span></div>
                </div>
            </x-ui.card>
        </div>
    @endif

    @if($tab === 'security')
        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card title="Change password" subtitle="Use a strong, unique password.">
                <form wire:submit="updatePassword" class="space-y-4">
                    <div>
                        <label class="label">Current password</label>
                        <input wire:model="currentPassword" type="password" autocomplete="current-password" class="field @error('currentPassword') field-error @enderror">
                        @error('currentPassword')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">New password</label>
                        <input wire:model="newPassword" type="password" autocomplete="new-password" class="field @error('newPassword') field-error @enderror">
                        @error('newPassword')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Confirm new password</label>
                        <input wire:model="newPasswordConfirmation" type="password" autocomplete="new-password" class="field">
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="btn btn-primary">Update password</button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Recent sign-ins" padding="p-0">
                @forelse($sessions as $session)
                    <div class="flex items-center gap-3 px-5 py-3.5 border-b border-subtle last:border-0">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/12 text-emerald-500 grid place-items-center shrink-0">
                            <x-icon name="check-circle" class="w-4 h-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">Successful sign-in</p>
                            <p class="text-xs text-tertiary">{{ $session->ip_address ?? 'Unknown IP' }}</p>
                        </div>
                        <span class="text-xs text-tertiary shrink-0">{{ $session->created_at->diffForHumans(short: true) }}</span>
                    </div>
                @empty
                    <x-ui.empty-state compact icon="shield" title="No sign-in history yet" />
                @endforelse
            </x-ui.card>
        </div>
    @endif

    @if($tab === 'notifications')
        <x-ui.card title="Email preferences" subtitle="Choose what lands in your inbox.">
            <form wire:submit="saveNotifications" class="space-y-3">
                @foreach([
                    ['emailProduct', 'Product updates', 'New features, improvements and release notes'],
                    ['emailMarketing', 'Tips & inspiration', 'Design ideas, guides and best practices'],
                    ['emailSecurity', 'Security alerts', 'Sign-ins from new devices and password changes'],
                    ['emailWeekly', 'Weekly digest', 'A summary of your website traffic each Monday'],
                ] as [$field, $label, $desc])
                    <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-surface-muted border border-subtle">
                        <div class="min-w-0">
                            <p class="font-medium text-sm">{{ $label }}</p>
                            <p class="text-xs text-tertiary mt-0.5">{{ $desc }}</p>
                        </div>
                        <button type="button" wire:click="$toggle('{{ $field }}')"
                                @class(['relative w-11 h-6 rounded-full transition-colors shrink-0',
                                        'bg-brand-500' => $$field, 'bg-ink-300 dark:bg-ink-700' => !$$field])>
                            <span @class(['absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all',
                                          'left-[22px]' => $$field, 'left-0.5' => !$$field])></span>
                        </button>
                    </div>
                @endforeach
                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn btn-primary">Save preferences</button>
                </div>
            </form>
        </x-ui.card>
    @endif

    @if($tab === 'api')
        <div class="space-y-6">
            @if($newKeyPlain)
                <div class="rounded-2xl bg-emerald-500/10 ring-1 ring-emerald-500/25 p-5">
                    <div class="flex items-start gap-3">
                        <x-icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm">Your new API key</p>
                            <p class="text-xs text-secondary mt-1">Copy it now — for security we will never show it again.</p>
                            <div class="flex items-center gap-2 mt-3" x-data="copyable(@js($newKeyPlain))">
                                <code class="flex-1 px-3 py-2 rounded-lg bg-surface border border-subtle font-mono text-xs truncate">{{ $newKeyPlain }}</code>
                                <button x-on:click="copy()" class="btn btn-secondary btn-sm shrink-0">
                                    <x-icon name="copy" class="w-3.5 h-3.5" /> <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                </button>
                            </div>
                        </div>
                        <button wire:click="$set('newKeyPlain', null)" class="btn btn-ghost btn-icon shrink-0"><x-icon name="x" class="w-4 h-4" /></button>
                    </div>
                </div>
            @endif

            <x-ui.card title="Create an API key" subtitle="Programmatic access to your websites and pages.">
                <form wire:submit="createApiKey" class="flex flex-col sm:flex-row gap-2">
                    <input wire:model="keyName" type="text" class="field flex-1 @error('keyName') field-error @enderror" placeholder="e.g. Production integration">
                    <button type="submit" class="btn btn-primary shrink-0"><x-icon name="plus" class="w-4 h-4" /> Generate key</button>
                </form>
                @error('keyName')<p class="error-text">{{ $message }}</p>@enderror
            </x-ui.card>

            <x-ui.card title="Your API keys" padding="p-0">
                @forelse($apiKeys as $key)
                    <div class="flex items-center gap-4 px-5 py-4 border-b border-subtle last:border-0">
                        <span class="w-9 h-9 rounded-lg bg-brand-500/10 text-brand-500 grid place-items-center shrink-0">
                            <x-icon name="key" class="w-4 h-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate">{{ $key->name }}</p>
                            <p class="text-xs text-tertiary font-mono">{{ $key->masked_key ?? $key->prefix.'••••••••' }}</p>
                        </div>
                        <div class="hidden sm:block text-right shrink-0">
                            <p class="text-xs text-tertiary">{{ number_format($key->usage_count) }} requests</p>
                            <p class="text-[11px] text-tertiary">{{ $key->last_used_at?->diffForHumans() ?? 'Never used' }}</p>
                        </div>
                        <button wire:click="revokeApiKey({{ $key->id }})" class="btn btn-ghost btn-sm text-rose-500 shrink-0">Revoke</button>
                    </div>
                @empty
                    <x-ui.empty-state compact icon="key" title="No API keys yet" description="Generate one above to start using the REST API." />
                @endforelse
            </x-ui.card>
        </div>
    @endif

    @if($tab === 'danger')
        <x-ui.card padding="p-0" class="ring-1 ring-rose-500/20">
            <div class="px-6 py-4 border-b border-subtle">
                <p class="font-semibold text-rose-600 dark:text-rose-400">Delete your account</p>
                <p class="text-sm text-tertiary mt-0.5">This is permanent and cannot be undone.</p>
            </div>
            <div class="p-6">
                <p class="text-sm text-secondary">
                    Deleting your account will permanently remove all of your websites, pages, media and billing
                    history. Published sites will go offline immediately.
                </p>
                <form wire:submit="deleteAccount" class="mt-5 space-y-3 max-w-sm">
                    <div>
                        <label class="label">Type <code class="font-mono text-rose-500">DELETE</code> to confirm</label>
                        <input wire:model="deleteConfirmation" type="text" class="field @error('deleteConfirmation') field-error @enderror" placeholder="DELETE">
                        @error('deleteConfirmation')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn btn-danger"><x-icon name="trash" class="w-4 h-4" /> Permanently delete account</button>
                </form>
            </div>
        </x-ui.card>
    @endif
</div>
