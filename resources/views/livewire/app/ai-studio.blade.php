<div class="space-y-6">

    <x-ui.page-header title="AI Studio" description="Describe what you need — get production-ready results in seconds.">
        <x-ui.badge color="violet">
            <x-icon name="zap" class="w-3.5 h-3.5" />
            {{ $creditsLeft > 1000000 ? 'Unlimited' : number_format($creditsLeft) }} credits left
        </x-ui.badge>
    </x-ui.page-header>

    {{-- tool picker --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        @foreach($tools as $key => $t)
            <button wire:click="setTool('{{ $key }}')"
                    @class([
                        'card p-4 text-left transition-all duration-200 group',
                        'ring-2 ring-brand-500 shadow-glow' => $tool === $key,
                        'card-hover' => $tool !== $key,
                    ])>
                <span @class([
                    'w-10 h-10 rounded-xl grid place-items-center mb-3 transition-transform group-hover:scale-105',
                    'bg-gradient-to-br from-brand-500 to-violet-500 text-white shadow-lg shadow-brand-500/25' => $tool === $key,
                    'bg-surface-muted text-tertiary' => $tool !== $key,
                ])>
                    <x-icon :name="$t['icon']" class="w-5 h-5" />
                </span>
                <p class="font-semibold text-sm leading-tight">{{ $t['label'] }}</p>
                <p class="text-xs text-tertiary mt-1 leading-snug">{{ $t['desc'] }}</p>
                <span class="inline-flex items-center gap-1 mt-2.5 text-[10px] font-bold uppercase tracking-wide text-tertiary">
                    <x-icon name="zap" class="w-3 h-3" /> {{ $t['credits'] }} {{ Str::plural('credit', $t['credits']) }}
                </span>
            </button>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- ============ prompt form ============ --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card :title="$tools[$tool]['label']" :subtitle="$tools[$tool]['desc']">
                <form wire:submit="generate" class="space-y-4">
                    <div>
                        <label class="label">
                            @switch($tool)
                                @case('website') @case('landing') Describe your business or project @break
                                @case('blog') What should the article be about? @break
                                @case('palette') Describe the mood or brand @break
                                @case('images') What kind of imagery do you need? @break
                                @default What do you need written?
                            @endswitch
                        </label>
                        <textarea wire:model="prompt" rows="4"
                                  class="field resize-none @error('prompt') field-error @enderror"
                                  placeholder="{{ match($tool) {
                                      'website' => 'A modern SaaS platform that helps ecommerce teams understand their customer data…',
                                      'landing' => 'A launch page for our new AI analytics product, focused on free trial signups…',
                                      'blog' => 'How small businesses can improve website conversion rates',
                                      'palette' => 'Calm, trustworthy and premium — for a healthcare brand',
                                      'images' => 'Hero and section imagery for a boutique architecture studio',
                                      'design' => 'Review the layout of a minimal portfolio site',
                                      default => 'A punchy headline for our pricing page',
                                  } }}"></textarea>
                        @error('prompt')<p class="error-text">{{ $message }}</p>@enderror
                        <p class="hint">Be specific — mention your audience, product and what makes you different.</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Industry</label>
                            <select wire:model="industry" class="field">
                                @foreach($industries as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Tone of voice</label>
                            <select wire:model="tone" class="field">
                                @foreach($tones as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    @if(in_array($tool, ['website', 'landing']))
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Brand name <span class="text-tertiary font-normal">(optional)</span></label>
                                <input wire:model="businessName" type="text" class="field" placeholder="Acme Inc.">
                            </div>
                            <div>
                                <label class="label">Colour scheme</label>
                                <select wire:model="colorScheme" class="field">
                                    <option value="">Auto (recommended)</option>
                                    @foreach(['indigo'=>'Indigo Nebula','emerald'=>'Evergreen','sunset'=>'Sunset Coral','ocean'=>'Deep Ocean','rose'=>'Rose Quartz','violet'=>'Ultraviolet','slate'=>'Monochrome','amber'=>'Golden Hour','teal'=>'Lagoon'] as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    @if(in_array($tool, ['landing', 'blog']) && $websites->count())
                        <div>
                            <label class="label">Add to website</label>
                            <select wire:model="targetWebsite" class="field">
                                <option value="">Most recent website</option>
                                @foreach($websites as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                            </select>
                        </div>
                    @endif

                    @if($tool === 'content')
                        <div>
                            <label class="label">Content type</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['headline'=>'Headline','tagline'=>'Tagline','paragraph'=>'Paragraph','bullets'=>'Bullet points','cta'=>'Call to action','about'=>'About section'] as $k => $v)
                                    <button type="button" wire:click="$set('contentType','{{ $k }}')"
                                            @class([
                                                'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border',
                                                'bg-brand-500/12 border-brand-500/30 text-brand-600 dark:text-brand-300' => $contentType === $k,
                                                'border-subtle text-tertiary hover:text-secondary' => $contentType !== $k,
                                            ])>{{ $v }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="generate">
                        <span wire:loading.remove wire:target="generate" class="flex items-center gap-2">
                            <x-icon name="sparkles" class="w-4 h-4" /> Generate now
                        </span>
                        <span wire:loading wire:target="generate" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            Composing your {{ strtolower($tools[$tool]['label']) }}…
                        </span>
                    </button>
                </form>
            </x-ui.card>

            {{-- loading skeleton --}}
            <div wire:loading wire:target="generate" class="card p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="skeleton w-10 h-10 rounded-xl"></div>
                    <div class="flex-1 space-y-2"><div class="skeleton h-3.5 w-1/3"></div><div class="skeleton h-3 w-1/2"></div></div>
                </div>
                <div class="skeleton h-40 w-full"></div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="skeleton h-20"></div><div class="skeleton h-20"></div><div class="skeleton h-20"></div>
                </div>
            </div>

            {{-- ============ results ============ --}}
            @if($result)
                <div class="animate-[fade-up_.45s_cubic-bezier(.16,1,.3,1)_both]">
                    @if($tool === 'blog')
                        <x-ui.card :title="$result['title'] ?? 'Draft article'" subtitle="{{ $result['reading_minutes'] ?? 3 }} min read">
                            <x-slot:actions>
                                <button wire:click="saveBlogPost" class="btn btn-primary btn-sm">
                                    <x-icon name="check" class="w-3.5 h-3.5" /> Save as draft
                                </button>
                            </x-slot:actions>
                            <div class="prose-sm max-w-none space-y-3 [&_h2]:text-lg [&_h2]:font-bold [&_h2]:mt-5 [&_h3]:font-semibold [&_h3]:mt-4 [&_p]:text-secondary [&_blockquote]:border-l-2 [&_blockquote]:border-brand-500 [&_blockquote]:pl-4 [&_blockquote]:italic">
                                {!! $result['content'] ?? '' !!}
                            </div>
                            @if(!empty($result['tags']))
                                <div class="flex flex-wrap gap-1.5 mt-5 pt-4 border-t border-subtle">
                                    @foreach($result['tags'] as $tag)<x-ui.badge color="slate">#{{ $tag }}</x-ui.badge>@endforeach
                                </div>
                            @endif
                        </x-ui.card>

                    @elseif($tool === 'palette')
                        <div class="space-y-4">
                            @foreach($result['palettes'] ?? [] as $p)
                                <x-ui.card padding="p-5">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="font-semibold">{{ $p['name'] }}</p>
                                        <x-ui.badge color="emerald"><x-icon name="check" class="w-3 h-3" /> WCAG AA</x-ui.badge>
                                    </div>
                                    <div class="flex gap-2 flex-wrap">
                                        @foreach(['primary','secondary','accent','background','surface','text','muted'] as $role)
                                            <div x-data="copyable('{{ $p[$role] ?? '' }}')" x-on:click="copy()" class="cursor-pointer group">
                                                <div class="w-16 h-16 rounded-xl ring-1 ring-black/8 dark:ring-white/10 transition-transform group-hover:scale-105"
                                                     style="background: {{ $p[$role] ?? '#000' }}"></div>
                                                <p class="text-[10px] font-medium text-tertiary mt-1.5 capitalize">{{ $role }}</p>
                                                <p class="text-[10px] font-mono text-tertiary" x-text="copied ? 'Copied!' : '{{ $p[$role] ?? '' }}'"></p>
                                            </div>
                                        @endforeach
                                    </div>
                                </x-ui.card>
                            @endforeach
                        </div>

                    @elseif($tool === 'images')
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($result['suggestions'] ?? [] as $s)
                                <x-ui.card padding="p-5" hover>
                                    <div class="aspect-video rounded-xl bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center mb-3">
                                        <x-icon name="image" class="w-8 h-8 text-brand-500/50" />
                                    </div>
                                    <p class="font-semibold text-sm capitalize">{{ $s['keyword'] }}</p>
                                    <p class="text-xs text-tertiary mt-1">{{ $s['placement'] }} · {{ $s['aspect'] }}</p>
                                    <div class="mt-3 p-3 rounded-lg bg-surface-muted" x-data="copyable(@js($s['prompt']))">
                                        <p class="text-xs text-secondary font-mono leading-relaxed">{{ $s['prompt'] }}</p>
                                        <button x-on:click="copy()" class="btn btn-ghost btn-sm mt-2 w-full">
                                            <x-icon name="copy" class="w-3.5 h-3.5" />
                                            <span x-text="copied ? 'Copied!' : 'Copy prompt'"></span>
                                        </button>
                                    </div>
                                </x-ui.card>
                            @endforeach
                        </div>

                    @elseif($tool === 'design')
                        <div class="space-y-4">
                            <x-ui.card title="Recommended palette" padding="p-5">
                                <div class="flex gap-2 flex-wrap">
                                    @foreach($result['palette']['swatches'] ?? [] as $c)
                                        <div class="w-20 h-20 rounded-xl ring-1 ring-black/8 dark:ring-white/10" style="background: {{ $c }}"></div>
                                    @endforeach
                                </div>
                                @if(!empty($result['typography']))
                                    <div class="mt-5 pt-4 border-t border-subtle grid sm:grid-cols-2 gap-4 text-sm">
                                        <div><span class="text-tertiary text-xs uppercase tracking-wide font-semibold">Heading</span><p class="font-semibold mt-1">{{ $result['typography']['heading'] }}</p></div>
                                        <div><span class="text-tertiary text-xs uppercase tracking-wide font-semibold">Body</span><p class="font-semibold mt-1">{{ $result['typography']['body'] }}</p></div>
                                    </div>
                                @endif
                            </x-ui.card>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach($result['recommendations'] ?? [] as $rec)
                                    <x-ui.card padding="p-5" hover>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="w-7 h-7 rounded-lg bg-brand-500/12 text-brand-500 grid place-items-center">
                                                <x-icon name="wand" class="w-3.5 h-3.5" />
                                            </span>
                                            <p class="font-semibold text-sm">{{ $rec['area'] }}</p>
                                        </div>
                                        <p class="text-sm text-secondary leading-relaxed">{{ $rec['advice'] }}</p>
                                    </x-ui.card>
                                @endforeach
                            </div>
                        </div>

                    @else
                        <x-ui.card title="Generated copy" subtitle="{{ count($result['variants'] ?? []) }} variations">
                            <div class="space-y-3">
                                @foreach($result['variants'] ?? [] as $i => $variant)
                                    <div class="p-4 rounded-xl bg-surface-muted border border-subtle group" x-data="copyable(@js($variant))">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="text-sm leading-relaxed flex-1">{{ $variant }}</p>
                                            <button x-on:click="copy()" class="btn btn-ghost btn-icon shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" :title="copied ? 'Copied!' : 'Copy'">
                                                <x-icon name="copy" class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-tertiary mt-2">Variation {{ $i + 1 }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card>
                    @endif
                </div>
            @endif
        </div>

        {{-- ============ sidebar ============ --}}
        <div class="space-y-6">
            <x-ui.card padding="p-0" title="Recent generations">
                @forelse($history as $h)
                    <div class="flex items-start gap-3 px-5 py-3.5 border-b border-subtle last:border-0">
                        <span class="w-8 h-8 rounded-lg bg-violet-500/12 text-violet-500 grid place-items-center shrink-0">
                            <x-icon :name="$h->type->icon()" class="w-4 h-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate">{{ $h->type->label() }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($h->prompt, 40) }}</p>
                            <p class="text-[11px] text-tertiary mt-0.5">
                                {{ $h->created_at->diffForHumans(short: true) }} · {{ $h->credits_used }} credits
                            </p>
                        </div>
                        @if($h->status === 'failed')<x-ui.badge color="rose">Failed</x-ui.badge>@endif
                    </div>
                @empty
                    <x-ui.empty-state compact icon="sparkles" title="No generations yet" description="Your history will appear here." />
                @endforelse
            </x-ui.card>

            <div class="rounded-2xl p-5 bg-gradient-to-br from-brand-500/10 via-violet-500/8 to-cyan-400/8 ring-1 ring-brand-500/15">
                <x-icon name="zap" class="w-5 h-5 text-brand-500 mb-3" />
                <p class="font-semibold text-sm">Tips for better results</p>
                <ul class="mt-2.5 space-y-2 text-xs text-secondary">
                    <li class="flex gap-2"><span class="text-brand-500 font-bold">·</span> Name your audience explicitly.</li>
                    <li class="flex gap-2"><span class="text-brand-500 font-bold">·</span> Describe the outcome you sell, not features.</li>
                    <li class="flex gap-2"><span class="text-brand-500 font-bold">·</span> Mention a competitor or reference style.</li>
                    <li class="flex gap-2"><span class="text-brand-500 font-bold">·</span> Everything generated stays fully editable.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
