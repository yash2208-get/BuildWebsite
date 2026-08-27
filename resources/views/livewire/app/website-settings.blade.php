<div class="space-y-6">

    <x-ui.page-header :title="$website->name" description="Configure domains, SEO, and custom code."
        :breadcrumbs="[['label' => 'Websites', 'url' => route('app.websites')], ['label' => $website->name]]">
        <a href="{{ route('site.preview', $website->subdomain) }}" target="_blank" class="btn btn-secondary">
            <x-icon name="external" class="w-4 h-4" /> Preview
        </a>
        <a href="{{ route('builder.edit', $website) }}" class="btn btn-primary">
            <x-icon name="edit" class="w-4 h-4" /> Open builder
        </a>
    </x-ui.page-header>

    {{-- tabs --}}
    <div class="flex gap-1 p-1 rounded-xl bg-surface-muted border border-subtle overflow-x-auto scrollbar-thin">
        @foreach([
            'general' => ['General', 'settings'],
            'domain' => ['Domain', 'globe'],
            'seo' => ['SEO', 'search'],
            'code' => ['Custom code', 'code'],
            'danger' => ['Danger zone', 'alert'],
        ] as $key => [$label, $icon])
            <button wire:click="$set('tab','{{ $key }}')"
                    @class([
                        'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all',
                        'bg-surface shadow-sm text-primary' => $tab === $key,
                        'text-tertiary hover:text-secondary' => $tab !== $key,
                    ])>
                <x-icon :name="$icon" class="w-4 h-4" /> {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ============= GENERAL ============= --}}
    @if($tab === 'general')
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-ui.card title="General settings" subtitle="Basic details about this website.">
                    <form wire:submit="saveGeneral" class="space-y-4">
                        <div>
                            <label class="label">Website name</label>
                            <input wire:model="name" type="text" class="field @error('name') field-error @enderror">
                            @error('name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label">Description</label>
                            <textarea wire:model="description" rows="3" class="field"></textarea>
                        </div>
                        <div>
                            <label class="label">Category</label>
                            <select wire:model="category" class="field">
                                @foreach($categories as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Free subdomain</label>
                            <div class="flex">
                                <input wire:model="subdomain" type="text"
                                       class="field rounded-r-none @error('subdomain') field-error @enderror">
                                <span class="inline-flex items-center px-3.5 rounded-r-xl border border-l-0 border-subtle bg-surface-muted text-sm text-tertiary whitespace-nowrap">
                                    .{{ $subdomainHost }}
                                </span>
                            </div>
                            @error('subdomain')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-xl bg-surface-muted border border-subtle">
                            <div>
                                <p class="font-medium text-sm">Maintenance mode</p>
                                <p class="text-xs text-tertiary mt-0.5">Show a holding page to visitors while you work.</p>
                            </div>
                            <button type="button" wire:click="$toggle('maintenanceMode')"
                                    @class(['relative w-11 h-6 rounded-full transition-colors shrink-0', 'bg-brand-500' => $maintenanceMode, 'bg-ink-300 dark:bg-ink-700' => !$maintenanceMode])>
                                <span @class(['absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all', 'left-[22px]' => $maintenanceMode, 'left-0.5' => !$maintenanceMode])></span>
                            </button>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save changes</button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <div class="space-y-4">
                <x-ui.card title="Status" padding="p-5">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-tertiary">Status</span>
                            <x-ui.badge :color="$website->status->color()" dot>{{ $website->status->label() }}</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between"><span class="text-tertiary">Pages</span><span class="font-semibold tabular-nums">{{ $website->pages_count }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-tertiary">Total views</span><span class="font-semibold tabular-nums">{{ number_format($website->views_count) }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-tertiary">Created</span><span class="font-medium">{{ $website->created_at->format('M j, Y') }}</span></div>
                        @if($website->published_at)
                            <div class="flex items-center justify-between"><span class="text-tertiary">Published</span><span class="font-medium">{{ $website->published_at->format('M j, Y') }}</span></div>
                        @endif
                    </div>
                </x-ui.card>
                <x-ui.card title="Quick actions" padding="p-5">
                    <div class="space-y-2">
                        <a href="{{ route('app.analytics') }}" class="nav-link"><x-icon name="chart" class="w-4 h-4" /> View analytics</a>
                        @can('export', $website)
                            <a href="{{ route('website.export', $website) }}" class="nav-link"><x-icon name="download" class="w-4 h-4" /> Export as HTML</a>
                        @endcan
                        <a href="{{ route('app.websites') }}" class="nav-link"><x-icon name="copy" class="w-4 h-4" /> Duplicate site</a>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- ============= DOMAIN ============= --}}
    @if($tab === 'domain')
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card title="Free subdomain" subtitle="Every site gets one, available immediately.">
                    <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/8 ring-1 ring-emerald-500/15">
                        <x-icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0" />
                        <code class="text-sm font-mono flex-1 truncate">{{ $website->subdomain }}.{{ $subdomainHost }}</code>
                        <a href="{{ route('site.preview', $website->subdomain) }}" target="_blank" class="btn btn-ghost btn-sm">
                            <x-icon name="external" class="w-3.5 h-3.5" /> Visit
                        </a>
                    </div>
                </x-ui.card>

                <x-ui.card title="Custom domain" subtitle="Connect a domain you already own.">
                    <form wire:submit="saveDomain" class="space-y-4">
                        <div>
                            <label class="label">Domain name</label>
                            <input wire:model="customDomain" type="text" class="field @error('customDomain') field-error @enderror" placeholder="www.example.com">
                            @error('customDomain')<p class="error-text">{{ $message }}</p>@enderror
                            <p class="hint">Enter the bare hostname without http:// or a trailing slash.</p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="submit" class="btn btn-primary">Save domain</button>
                        </div>
                    </form>

                    @if($website->custom_domain)
                        <div class="mt-6 pt-5 border-t border-subtle">
                            <div class="flex items-center justify-between mb-4">
                                <p class="font-semibold text-sm">DNS configuration</p>
                                @if($website->domain_verified)
                                    <x-ui.badge color="emerald" dot>Verified · SSL active</x-ui.badge>
                                @else
                                    <button wire:click="verifyDomain" class="btn btn-secondary btn-sm">
                                        <x-icon name="refresh" class="w-3.5 h-3.5" /> Verify now
                                    </button>
                                @endif
                            </div>
                            <p class="text-sm text-secondary mb-3">Add these records at your DNS provider. Propagation usually takes 5–30 minutes.</p>
                            <div class="table-wrap">
                                <table class="tbl">
                                    <thead><tr><th>Type</th><th>Name</th><th>Value</th><th></th></tr></thead>
                                    <tbody>
                                        @foreach([['A', '@', $dnsTarget], ['CNAME', 'www', $website->subdomain.'.'.$subdomainHost]] as [$type, $host, $value])
                                            <tr>
                                                <td><x-ui.badge color="slate">{{ $type }}</x-ui.badge></td>
                                                <td class="font-mono text-xs">{{ $host }}</td>
                                                <td class="font-mono text-xs">{{ $value }}</td>
                                                <td class="text-right">
                                                    <button x-data="copyable(@js($value))" x-on:click="copy()" class="btn btn-ghost btn-icon">
                                                        <x-icon name="copy" class="w-4 h-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </x-ui.card>
            </div>

            <div class="rounded-2xl p-5 bg-gradient-to-br from-brand-500/10 to-cyan-400/8 ring-1 ring-brand-500/15 h-fit">
                <x-icon name="shield" class="w-5 h-5 text-brand-500 mb-3" />
                <p class="font-semibold text-sm">Automatic HTTPS</p>
                <p class="text-xs text-secondary mt-2 leading-relaxed">
                    Once your DNS records resolve, we provision and auto-renew a free SSL certificate for your
                    domain. No configuration needed.
                </p>
            </div>
        </div>
    @endif

    {{-- ============= SEO ============= --}}
    @if($tab === 'seo')
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-ui.card title="Search engine optimisation" subtitle="Control how your site appears in search results.">
                    <form wire:submit="saveSeo" class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="label mb-0">Meta title</label>
                                <span @class(['text-xs tabular-nums', 'text-rose-500' => strlen($metaTitle) > 60, 'text-tertiary' => strlen($metaTitle) <= 60])>{{ strlen($metaTitle) }}/60</span>
                            </div>
                            <input wire:model.live="metaTitle" type="text" class="field" placeholder="{{ $website->name }} — Tagline here">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="label mb-0">Meta description</label>
                                <span @class(['text-xs tabular-nums', 'text-rose-500' => strlen($metaDescription) > 160, 'text-tertiary' => strlen($metaDescription) <= 160])>{{ strlen($metaDescription) }}/160</span>
                            </div>
                            <textarea wire:model.live="metaDescription" rows="3" class="field" placeholder="A concise, compelling summary of this page."></textarea>
                        </div>
                        <div>
                            <label class="label">Keywords</label>
                            <input wire:model="metaKeywords" type="text" class="field" placeholder="design, agency, branding">
                        </div>
                        <div>
                            <label class="label">Social share image (OG image)</label>
                            <input wire:model="ogImage" type="url" class="field" placeholder="https://example.com/og.jpg">
                            <p class="hint">Recommended size: 1200 × 630 pixels.</p>
                        </div>

                        <div class="divider"></div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Google Analytics ID</label>
                                <input wire:model="googleAnalyticsId" type="text" class="field font-mono text-xs" placeholder="G-XXXXXXXXXX">
                            </div>
                            <div>
                                <label class="label">Meta Pixel ID</label>
                                <input wire:model="facebookPixelId" type="text" class="field font-mono text-xs" placeholder="000000000000000">
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-xl bg-surface-muted border border-subtle">
                            <div>
                                <p class="font-medium text-sm">Allow search engine indexing</p>
                                <p class="text-xs text-tertiary mt-0.5">Turn off to add a noindex directive.</p>
                            </div>
                            <button type="button" wire:click="$toggle('indexable')"
                                    @class(['relative w-11 h-6 rounded-full transition-colors shrink-0', 'bg-brand-500' => $indexable, 'bg-ink-300 dark:bg-ink-700' => !$indexable])>
                                <span @class(['absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all', 'left-[22px]' => $indexable, 'left-0.5' => !$indexable])></span>
                            </button>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn btn-primary">Save SEO settings</button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <x-ui.card title="Search preview" padding="p-5" class="h-fit">
                <div class="p-4 rounded-xl bg-white dark:bg-ink-900 border border-subtle">
                    <p class="text-xs text-emerald-700 dark:text-emerald-400 truncate">
                        {{ $website->custom_domain ?: $website->subdomain.'.'.$subdomainHost }}
                    </p>
                    <p class="text-[#1a0dab] dark:text-[#8ab4f8] text-base leading-snug mt-1 hover:underline cursor-pointer truncate">
                        {{ $metaTitle ?: $website->name }}
                    </p>
                    <p class="text-sm text-ink-600 dark:text-ink-400 mt-1 line-clamp-2 leading-snug">
                        {{ $metaDescription ?: ($website->description ?: 'Add a meta description to control this snippet.') }}
                    </p>
                </div>
                <p class="text-xs text-tertiary mt-3">This is approximately how your site appears on Google.</p>
            </x-ui.card>
        </div>
    @endif

    {{-- ============= CODE ============= --}}
    @if($tab === 'code')
        <x-ui.card title="Custom code" subtitle="Injected into every page of this website.">
            <form wire:submit="saveCode" class="space-y-5">
                <div>
                    <label class="label flex items-center gap-2"><x-icon name="code" class="w-4 h-4 text-brand-500" /> Custom CSS</label>
                    <textarea wire:model="customCss" rows="10" spellcheck="false"
                              class="field font-mono text-xs leading-relaxed" placeholder=".hero { background: linear-gradient(...); }"></textarea>
                </div>
                <div>
                    <label class="label flex items-center gap-2"><x-icon name="code" class="w-4 h-4 text-amber-500" /> Custom JavaScript</label>
                    <textarea wire:model="customJs" rows="10" spellcheck="false"
                              class="field font-mono text-xs leading-relaxed" placeholder="document.addEventListener('DOMContentLoaded', () => { ... });"></textarea>
                </div>
                <div class="grid lg:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Head scripts</label>
                        <textarea wire:model="headScripts" rows="5" spellcheck="false" class="field font-mono text-xs" placeholder="<!-- inserted before </head> -->"></textarea>
                    </div>
                    <div>
                        <label class="label">Body scripts</label>
                        <textarea wire:model="bodyScripts" rows="5" spellcheck="false" class="field font-mono text-xs" placeholder="<!-- inserted before </body> -->"></textarea>
                    </div>
                </div>

                <div class="rounded-xl bg-amber-500/8 ring-1 ring-amber-500/15 p-4 flex gap-3">
                    <x-icon name="alert" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                    <p class="text-xs text-secondary leading-relaxed">
                        Custom code runs on your published site exactly as written. Invalid JavaScript can break page
                        functionality — test on a draft before publishing.
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Save custom code</button>
                </div>
            </form>
        </x-ui.card>
    @endif

    {{-- ============= DANGER ============= --}}
    @if($tab === 'danger')
        <x-ui.card padding="p-0" class="ring-1 ring-rose-500/20">
            <div class="px-6 py-4 border-b border-subtle">
                <p class="font-semibold text-rose-600 dark:text-rose-400">Danger zone</p>
                <p class="text-sm text-tertiary mt-0.5">These actions have lasting consequences.</p>
            </div>
            <div class="divide-y divide-[rgb(var(--border-subtle))]">
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <p class="font-medium text-sm">{{ $website->isPublished() ? 'Unpublish website' : 'Publish website' }}</p>
                        <p class="text-xs text-tertiary mt-0.5">{{ $website->isPublished() ? 'Take the site offline immediately.' : 'Make the site publicly accessible.' }}</p>
                    </div>
                    <a href="{{ route('app.websites') }}" class="btn btn-secondary btn-sm shrink-0">Manage</a>
                </div>
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <p class="font-medium text-sm">Delete this website</p>
                        <p class="text-xs text-tertiary mt-0.5">Moves the site and all pages to trash.</p>
                    </div>
                    <a href="{{ route('app.websites') }}" class="btn btn-danger btn-sm shrink-0">
                        <x-icon name="trash" class="w-3.5 h-3.5" /> Delete
                    </a>
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
