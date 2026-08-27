<div class="space-y-6">
    <x-ui.page-header title="Roles & Permissions" :description="$totalPermissions.' granular permissions across '.$roles->count().' roles'">
        <button wire:click="$set('showCreate', true)" class="btn btn-primary">
            <x-icon name="plus" class="w-4 h-4" /> New role
        </button>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-[20rem_1fr] items-start">
        {{-- ── role list ── --}}
        <div class="space-y-3 lg:sticky lg:top-6">
            @foreach($roles as $r)
                <button wire:click="selectRole({{ $r->id }})"
                        @class(['w-full text-left card p-4 transition-all group',
                                'ring-2 ring-brand-500/45 shadow-glow' => $editing === $r->id,
                                'card-hover' => $editing !== $r->id])>
                    <div class="flex items-start gap-3">
                        <span @class(['w-10 h-10 rounded-xl grid place-items-center shrink-0 text-white shadow-lg bg-gradient-to-br',
                                      'from-violet-500 to-fuchsia-500 shadow-violet-500/25' => $r->name === 'super-admin',
                                      'from-sky-500 to-cyan-500 shadow-sky-500/25' => $r->name === 'admin',
                                      'from-slate-400 to-slate-500 shadow-slate-500/20' => !in_array($r->name, ['super-admin','admin'])])>
                            <x-icon :name="$r->name === 'super-admin' ? 'crown' : ($r->name === 'admin' ? 'shield' : 'user')" class="w-5 h-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate">{{ Str::headline($r->name) }}</p>
                            <p class="text-xs text-tertiary mt-0.5">
                                {{ $r->users_count }} {{ Str::plural('user', $r->users_count) }} ·
                                {{ $r->name === 'super-admin' ? 'all' : $r->permissions_count }} {{ Str::plural('permission', $r->permissions_count) }}
                            </p>
                        </div>
                        <x-icon name="chevron-right" class="w-4 h-4 text-tertiary opacity-0 group-hover:opacity-100 transition-opacity shrink-0 mt-2.5" />
                    </div>
                </button>
            @endforeach
        </div>

        {{-- ── permission matrix ── --}}
        <div>
            @if($role)
                <x-ui.card padding="p-0">
                    <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-5 border-b border-subtle">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold">{{ Str::headline($role->name) }}</h2>
                            <p class="text-sm text-tertiary mt-0.5">
                                {{ count($granted) }} of {{ $totalPermissions }} permissions ·
                                {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }} assigned
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @unless(in_array($role->name, ['super-admin', 'admin', 'user']))
                                <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role? Users will lose its permissions."
                                        class="btn btn-ghost btn-sm text-rose-500">Delete role</button>
                            @endunless
                            <button wire:click="savePermissions" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="savePermissions">
                                <x-icon name="check" class="w-3.5 h-3.5" />
                                <span wire:loading.remove wire:target="savePermissions">Save changes</span>
                                <span wire:loading wire:target="savePermissions">Saving…</span>
                            </button>
                        </div>
                    </div>

                    @if($role->name === 'super-admin')
                        <div class="px-6 py-4 bg-violet-500/8 border-b border-subtle flex items-start gap-3">
                            <x-icon name="crown" class="w-5 h-5 text-violet-500 shrink-0 mt-0.5" />
                            <p class="text-sm text-secondary">
                                <strong class="text-primary">Super Admin bypasses all permission checks.</strong>
                                Changes here are recorded for auditing, but this role always has unrestricted access.
                            </p>
                        </div>
                    @endif

                    <div class="divide-y divide-[rgb(var(--border-subtle))]">
                        @foreach($catalogue as $group => $permissions)
                            @php
                                $keys = array_keys($permissions);
                                $groupGranted = count(array_intersect($keys, $granted));
                                $allOn = $groupGranted === count($keys);
                            @endphp
                            <section x-data="{ open: true }" class="px-6 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <button x-on:click="open = !open" class="flex items-center gap-2 min-w-0 group">
                                        <x-icon name="chevron-down" class="w-4 h-4 text-tertiary transition-transform shrink-0" x-bind:class="open || 'rotate-[-90deg]'" />
                                        <span class="font-semibold text-sm truncate">{{ $group }}</span>
                                        <span @class(['text-xs px-2 py-0.5 rounded-full tabular-nums shrink-0',
                                                      'bg-brand-500/12 text-brand-600 dark:text-brand-300' => $groupGranted > 0,
                                                      'bg-surface-muted text-tertiary' => $groupGranted === 0])>
                                            {{ $groupGranted }}/{{ count($keys) }}
                                        </span>
                                    </button>
                                    <button wire:click="toggleGroup('{{ $group }}')" class="btn btn-ghost btn-sm shrink-0">
                                        {{ $allOn ? 'Clear all' : 'Select all' }}
                                    </button>
                                </div>

                                <div x-show="open" x-collapse class="grid sm:grid-cols-2 gap-2 mt-3">
                                    @foreach($permissions as $key => $label)
                                        <button type="button" wire:click="togglePermission('{{ $key }}')" wire:key="p-{{ $key }}"
                                                @class(['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left transition-all border',
                                                        'bg-brand-500/8 border-brand-500/30' => in_array($key, $granted),
                                                        'border-subtle hover:border-brand-500/20 hover:bg-surface-muted' => !in_array($key, $granted)])>
                                            <span @class(['w-4.5 h-4.5 rounded-md grid place-items-center shrink-0 transition-all',
                                                          'bg-brand-500 text-white' => in_array($key, $granted),
                                                          'border border-subtle' => !in_array($key, $granted)])
                                                  style="width:1.125rem;height:1.125rem">
                                                @if(in_array($key, $granted))<x-icon name="check" class="w-3 h-3" />@endif
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block text-sm truncate">{{ $label }}</span>
                                                <code class="block text-[11px] text-tertiary font-mono truncate">{{ $key }}</code>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </x-ui.card>
            @else
                <x-ui.card>
                    <x-ui.empty-state icon="shield" title="Select a role"
                                      description="Choose a role on the left to review and edit its permission matrix." />
                </x-ui.card>
            @endif
        </div>
    </div>

    @if($showCreate)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('showCreate', false)"></div>
            <div class="relative card shadow-2xl max-w-md w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <h3 class="text-lg font-semibold">Create a role</h3>
                <p class="text-sm text-tertiary mt-1">Start with no permissions, then grant what you need.</p>
                <form wire:submit="createRole" class="space-y-4 mt-5">
                    <div>
                        <label class="label">Role name</label>
                        <input wire:model="name" type="text" class="field @error('name') field-error @enderror" placeholder="content-editor">
                        <p class="hint">Lowercase, hyphen separated.</p>
                        @error('name')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Description <span class="text-tertiary font-normal">(optional)</span></label>
                        <input wire:model="description" type="text" class="field" placeholder="Can manage blog posts and pages">
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showCreate', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create role</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
