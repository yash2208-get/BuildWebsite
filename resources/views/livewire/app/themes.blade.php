<div class="space-y-6" x-data="{ builderOpen: @entangle('showBuilder') }">
    <x-ui.page-header title="Theme Builder" description="Craft colour systems and typography that cascade across every page.">
        <button x-on:click="builderOpen = true" class="btn btn-primary">
            <x-icon name="plus" class="w-4 h-4" /> Create theme
        </button>
    </x-ui.page-header>

    @if($myThemes->isNotEmpty())
        <div>
            <h2 class="text-xs font-bold uppercase tracking-[.12em] text-tertiary mb-4">Your themes</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($myThemes as $theme)
                    @include('livewire.app.partials.theme-card', ['theme' => $theme, 'owned' => true])
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h2 class="text-xs font-bold uppercase tracking-[.12em] text-tertiary mb-4">System themes</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($systemThemes as $theme)
                @include('livewire.app.partials.theme-card', ['theme' => $theme, 'owned' => false])
            @endforeach
        </div>
    </div>

    {{-- apply modal --}}
    @if($applyingTo)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('applyingTo', null)"></div>
            <div class="relative card shadow-2xl max-w-md w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <h3 class="font-semibold text-lg">Apply theme to a website</h3>
                <p class="text-sm text-secondary mt-1">Choose which site should use this theme.</p>
                <div class="space-y-2 mt-5 max-h-72 overflow-y-auto scrollbar-thin">
                    @forelse($websites as $site)
                        <button wire:click="applyTheme({{ $applyingTo }}, {{ $site->id }})"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-subtle hover:border-brand-500/40 hover:bg-brand-500/5 transition-all text-left">
                            <x-icon name="globe" class="w-4 h-4 text-brand-500 shrink-0" />
                            <span class="text-sm font-medium flex-1 truncate">{{ $site->name }}</span>
                            @if($site->theme_id === $applyingTo)<x-ui.badge color="emerald">Current</x-ui.badge>@endif
                        </button>
                    @empty
                        <p class="text-sm text-tertiary text-center py-6">Create a website first.</p>
                    @endforelse
                </div>
                <button wire:click="$set('applyingTo', null)" class="btn btn-ghost w-full mt-4">Cancel</button>
            </div>
        </div>
    @endif

    {{-- theme builder modal --}}
    <x-ui.modal show="builderOpen" title="Create a custom theme" max-width="max-w-2xl">
        <form wire:submit="saveCustomTheme" class="space-y-5">
            <div>
                <label class="label">Theme name</label>
                <input wire:model="themeName" type="text" class="field @error('themeName') field-error @enderror" placeholder="Midnight Aurora">
                @error('themeName')<p class="error-text">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Colour system</label>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @foreach([['primary','Primary'],['secondary','Secondary'],['accent','Accent'],['background','Background'],['text','Text']] as [$field, $label])
                        <div>
                            <div class="relative">
                                <input type="color" wire:model.live="{{ $field }}"
                                       class="w-full h-14 rounded-xl border border-subtle cursor-pointer bg-transparent p-1">
                            </div>
                            <p class="text-[11px] font-medium text-secondary mt-1.5">{{ $label }}</p>
                            <p class="text-[10px] font-mono text-tertiary">{{ $$field }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="label">Heading font</label>
                    <select wire:model.live="headingFont" class="field">
                        @foreach($fonts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Body font</label>
                    <select wire:model.live="bodyFont" class="field">
                        @foreach($fonts as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Corner radius</label>
                    <select wire:model.live="radius" class="field">
                        @foreach(['0px'=>'Sharp','4px'=>'Subtle','8px'=>'Rounded','12px'=>'Soft','20px'=>'Pill'] as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- live preview --}}
            <div>
                <label class="label">Live preview</label>
                <div class="rounded-xl overflow-hidden border border-subtle p-6"
                     style="background: {{ $background }}; color: {{ $text }}; font-family: '{{ $bodyFont }}', sans-serif;">
                    <p style="font-family: '{{ $headingFont }}', sans-serif; font-size: 26px; font-weight: 800; letter-spacing: -.02em; line-height:1.15;">
                        Design that feels effortless
                    </p>
                    <p style="opacity:.7; font-size: 14px; margin-top: 10px; line-height:1.6;">
                        This preview updates live as you adjust your colour system and typography.
                    </p>
                    <div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                        <span style="background:{{ $primary }}; color:#fff; padding:9px 18px; border-radius:{{ $radius }}; font-size:13px; font-weight:600;">Primary action</span>
                        <span style="background:{{ $secondary }}; color:#fff; padding:9px 18px; border-radius:{{ $radius }}; font-size:13px; font-weight:600;">Secondary</span>
                        <span style="border:1.5px solid {{ $accent }}; color:{{ $accent }}; padding:9px 18px; border-radius:{{ $radius }}; font-size:13px; font-weight:600;">Accent</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" class="btn btn-ghost" x-on:click="builderOpen = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save theme</button>
            </div>
        </form>
    </x-ui.modal>
</div>
