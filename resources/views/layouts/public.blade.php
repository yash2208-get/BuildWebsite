<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('meta_title', $title ?? ($brand['tagline'] ?? '')) — {{ $brand['name'] ?? config('platform.name') }}</title>
    <meta name="description" content="@yield('meta_description', $brand['tagline'] ?? '')">
    <meta property="og:title" content="@yield('meta_title', $title ?? '')">
    <meta property="og:description" content="@yield('meta_description', $brand['tagline'] ?? '')">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>(function(){const t=localStorage.getItem('aurora-theme')||'system';document.documentElement.classList.toggle('dark',t==='dark'||(t==='system'&&matchMedia('(prefers-color-scheme: dark)').matches));})();</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="antialiased">

{{-- ══════════════ NAV ══════════════ --}}
<header x-data="{ scrolled: false, mobile: false }"
        x-on:scroll.window="scrolled = window.scrollY > 20"
        :class="scrolled ? 'glass shadow-sm border-b border-subtle' : 'bg-transparent'"
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <nav class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="flex items-center justify-between h-16 lg:h-[72px]">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 via-violet-500 to-cyan-400 grid place-items-center shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform">
                    <x-icon name="layers" class="w-[18px] h-[18px] text-white" />
                </span>
                <span class="font-bold text-lg tracking-tight">{{ $brand['name'] ?? config('platform.name') }}</span>
            </a>

            <div class="hidden lg:flex items-center gap-1">
                @foreach([
                    ['Features', route('features')],
                    ['Templates', route('templates.public')],
                    ['Pricing', route('pricing')],
                    ['FAQ', route('faq')],
                    ['Contact', route('contact')],
                ] as [$label, $url])
                    <a href="{{ $url }}"
                       @class([
                           'px-3.5 py-2 rounded-lg text-sm font-medium transition-colors',
                           'text-primary bg-surface-muted' => url()->current() === $url,
                           'text-secondary hover:text-primary hover:bg-surface-muted' => url()->current() !== $url,
                       ])>{{ $label }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <button class="btn btn-ghost btn-icon" x-data x-on:click="$store.ui.cycleTheme()" aria-label="Toggle theme">
                    <x-icon name="sun" class="w-[18px] h-[18px] dark:hidden" />
                    <x-icon name="moon" class="w-[18px] h-[18px] hidden dark:block" />
                </button>

                @auth
                    <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn btn-primary btn-sm">
                        Dashboard <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Sign in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Start free</a>
                @endauth

                <button class="btn btn-ghost btn-icon lg:hidden" x-on:click="mobile = !mobile" aria-label="Menu">
                    <x-icon name="menu" class="w-5 h-5" x-show="!mobile" />
                    <x-icon name="x" class="w-5 h-5" x-show="mobile" x-cloak />
                </button>
            </div>
        </div>

        {{-- mobile menu --}}
        <div x-show="mobile" x-cloak x-collapse class="lg:hidden pb-4 space-y-1">
            @foreach([['Features', route('features')], ['Templates', route('templates.public')], ['Pricing', route('pricing')], ['FAQ', route('faq')], ['Contact', route('contact')]] as [$label, $url])
                <a href="{{ $url }}" class="block px-3.5 py-2.5 rounded-lg text-sm font-medium text-secondary hover:bg-surface-muted">{{ $label }}</a>
            @endforeach
            @guest
                <a href="{{ route('login') }}" class="block px-3.5 py-2.5 rounded-lg text-sm font-medium text-secondary hover:bg-surface-muted sm:hidden">Sign in</a>
            @endguest
        </div>
    </nav>
</header>

<main class="pt-16 lg:pt-[72px]">
    {{ $slot ?? '' }}
    @yield('content')
</main>

{{-- ══════════════ FOOTER ══════════════ --}}
<footer class="border-t border-subtle bg-surface-muted mt-24">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 py-16">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 w-fit">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 via-violet-500 to-cyan-400 grid place-items-center shadow-lg shadow-brand-500/25">
                        <x-icon name="layers" class="w-[18px] h-[18px] text-white" />
                    </span>
                    <span class="font-bold text-lg tracking-tight">{{ $brand['name'] ?? config('platform.name') }}</span>
                </a>
                <p class="text-sm text-secondary mt-4 max-w-xs leading-relaxed">
                    {{ $brand['tagline'] ?? config('platform.tagline') }}
                </p>
                <div class="flex items-center gap-2 mt-5">
                    @foreach(['twitter', 'github', 'linkedin', 'youtube'] as $social)
                        <a href="#" class="w-9 h-9 rounded-lg bg-surface border border-subtle grid place-items-center text-tertiary hover:text-brand-500 hover:border-brand-500/30 transition-colors" aria-label="{{ $social }}">
                            <x-icon :name="$social" class="w-4 h-4" />
                        </a>
                    @endforeach
                </div>
            </div>

            @foreach([
                'Product' => [['Features', route('features')], ['Templates', route('templates.public')], ['Pricing', route('pricing')], ['AI Studio', route('register')]],
                'Company' => [['About', route('page.show', 'about')], ['Contact', route('contact')], ['Blog', route('page.show', 'blog')], ['Careers', route('page.show', 'careers')]],
                'Resources' => [['FAQ', route('faq')], ['Help center', route('contact')], ['API docs', route('page.show', 'api')], ['Status', route('page.show', 'status')]],
            ] as $group => $links)
                <div>
                    <p class="font-semibold text-sm mb-4">{{ $group }}</p>
                    <ul class="space-y-2.5">
                        @foreach($links as [$label, $url])
                            <li><a href="{{ $url }}" class="text-sm text-secondary hover:text-brand-500 transition-colors">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-14 pt-8 border-t border-subtle">
            <p class="text-xs text-tertiary">© {{ date('Y') }} {{ $brand['name'] ?? config('platform.name') }}. All rights reserved.</p>
            <div class="flex items-center gap-5">
                <a href="{{ route('page.show', 'privacy') }}" class="text-xs text-tertiary hover:text-secondary">Privacy</a>
                <a href="{{ route('page.show', 'terms') }}" class="text-xs text-tertiary hover:text-secondary">Terms</a>
                <a href="{{ route('page.show', 'cookies') }}" class="text-xs text-tertiary hover:text-secondary">Cookies</a>
            </div>
        </div>
    </div>
</footer>

@include('partials.toasts')
@livewireScripts
@stack('scripts')
</body>
</html>
