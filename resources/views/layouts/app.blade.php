<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'Dashboard') — {{ $brand['name'] ?? config('platform.name') }}</title>
    <meta name="description" content="{{ $brand['tagline'] ?? '' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        (function () {
            const t = localStorage.getItem('aurora-theme') || 'system';
            const dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen antialiased" x-data>

<div class="flex min-h-screen">

    {{-- ===================== Sidebar ===================== --}}
    <aside x-data
           class="fixed inset-y-0 left-0 z-50 w-[264px] flex flex-col glass border-r border-subtle transition-transform duration-300 lg:translate-x-0"
           :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        {{-- brand --}}
        <div class="h-16 flex items-center gap-2.5 px-5 border-b border-subtle shrink-0">
            <a href="{{ auth()->user()?->homeRoute() ? route(auth()->user()->homeRoute()) : url('/') }}"
               class="flex items-center gap-2.5 min-w-0 group">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 via-violet-500 to-cyan-400 grid place-items-center shadow-lg shadow-brand-500/30 group-hover:scale-105 transition-transform">
                    <x-icon name="layers" class="w-[18px] h-[18px] text-white" />
                </span>
                <span class="min-w-0">
                    <span class="block font-bold tracking-tight truncate leading-tight">{{ $brand['name'] ?? config('platform.name') }}</span>
                    <span class="block text-[10px] uppercase tracking-[0.14em] text-tertiary font-semibold">
                        {{ auth()->user()?->primaryRole()->label() ?? 'Guest' }}
                    </span>
                </span>
            </a>
            <button class="btn btn-ghost btn-icon ml-auto lg:hidden" x-on:click="$store.ui.sidebarOpen = false" aria-label="Close menu">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        {{-- nav --}}
        <nav class="flex-1 overflow-y-auto scroll-thin px-3 py-4 space-y-6">
            @foreach($navigation ?? [] as $group => $items)
                <div>
                    @if(! is_numeric($group))
                        <p class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-tertiary">{{ $group }}</p>
                    @endif
                    <div class="space-y-0.5">
                        @foreach($items as $item)
                            @continue(isset($item['can']) && ! auth()->user()?->can($item['can']))
                            @php $active = $item['active'] ?? request()->routeIs($item['pattern'] ?? $item['route'].'*'); @endphp
                            <a href="{{ $item['url'] ?? route($item['route']) }}"
                               @class(['nav-link group', 'nav-link-active' => $active])
                               @if($active) aria-current="page" @endif>
                                <x-icon :name="$item['icon'] ?? 'circle'" class="w-[18px] h-[18px] shrink-0" />
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if(! empty($item['badge']))
                                    <span class="ml-auto badge bg-brand-500/15 text-brand-600 dark:text-brand-300 text-[10px] px-1.5 py-0.5">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        {{-- usage / upgrade --}}
        @if(isset($usage) && ! auth()->user()?->isStaff())
            <div class="p-3 shrink-0">
                <div class="rounded-2xl p-4 bg-gradient-to-br from-brand-500/12 via-violet-500/8 to-cyan-400/8 ring-1 ring-brand-500/15">
                    <div class="flex items-center gap-2 mb-3">
                        <x-icon name="zap" class="w-4 h-4 text-brand-500" />
                        <span class="text-xs font-bold uppercase tracking-wider">{{ auth()->user()?->activePlan()?->name ?? 'Free' }} plan</span>
                    </div>
                    <div class="space-y-2.5">
                        <x-ui.progress :value="$usage['websites']['used']" :max="max(1,$usage['websites']['limit'])"
                                       label="Websites" :show-value="false" />
                        <p class="text-[11px] text-tertiary -mt-1">
                            {{ $usage['websites']['used'] }} of {{ $usage['websites']['limit'] < 0 ? '∞' : $usage['websites']['limit'] }}
                        </p>
                        <x-ui.progress :value="$usage['ai_credits']['used']" :max="max(1,$usage['ai_credits']['limit'])"
                                       label="AI credits" :show-value="false" color="sky" />
                        <p class="text-[11px] text-tertiary -mt-1">
                            {{ $usage['ai_credits']['used'] }} of {{ $usage['ai_credits']['limit'] < 0 ? '∞' : $usage['ai_credits']['limit'] }}
                        </p>
                    </div>
                    <a href="{{ route('app.billing') }}" class="btn btn-primary btn-sm w-full mt-3.5">Upgrade plan</a>
                </div>
            </div>
        @endif
    </aside>

    {{-- backdrop --}}
    <div x-show="$store.ui.sidebarOpen" x-cloak x-transition.opacity
         x-on:click="$store.ui.sidebarOpen = false"
         class="fixed inset-0 z-40 bg-ink-950/50 backdrop-blur-sm lg:hidden"></div>

    {{-- ===================== Main ===================== --}}
    <div class="flex-1 min-w-0 lg:ml-[264px] flex flex-col">

        <header class="sticky top-0 z-30 h-16 glass border-b border-subtle flex items-center gap-3 px-4 sm:px-6">
            <button class="btn btn-ghost btn-icon lg:hidden" x-on:click="$store.ui.sidebarOpen = true" aria-label="Open menu">
                <x-icon name="menu" class="w-5 h-5" />
            </button>

            @include('partials.search')

            <div class="ml-auto flex items-center gap-1.5">
                {{-- theme --}}
                <button class="btn btn-ghost btn-icon" x-on:click="$store.ui.cycleTheme()"
                        :title="'Theme: ' + $store.ui.theme" aria-label="Toggle theme">
                    <template x-if="$store.ui.theme === 'light'"><x-icon name="sun" class="w-[18px] h-[18px]" /></template>
                    <template x-if="$store.ui.theme === 'dark'"><x-icon name="moon" class="w-[18px] h-[18px]" /></template>
                    <template x-if="$store.ui.theme === 'system'"><x-icon name="monitor" class="w-[18px] h-[18px]" /></template>
                </button>

                @include('partials.notifications')
                @include('partials.user-menu')
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <footer class="px-6 py-5 text-xs text-tertiary flex flex-wrap items-center justify-between gap-3 border-t border-subtle">
            <span>© {{ date('Y') }} {{ $brand['name'] ?? config('platform.name') }}. All rights reserved.</span>
            <span class="flex items-center gap-4">
                <a href="{{ route('page.show', 'privacy') }}" class="hover:text-brand-500 transition-colors">Privacy</a>
                <a href="{{ route('page.show', 'terms') }}" class="hover:text-brand-500 transition-colors">Terms</a>
                <a href="{{ route('support.index') }}" class="hover:text-brand-500 transition-colors">Support</a>
            </span>
        </footer>
    </div>
</div>

@include('partials.toasts')

@livewireScripts
@stack('scripts')
</body>
</html>
