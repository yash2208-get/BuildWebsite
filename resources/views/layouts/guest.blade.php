<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'Sign in') — {{ $brand['name'] ?? config('platform.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>(function(){const t=localStorage.getItem('aurora-theme')||'system';document.documentElement.classList.toggle('dark',t==='dark'||(t==='system'&&matchMedia('(prefers-color-scheme: dark)').matches));})();</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">
<div class="min-h-screen grid lg:grid-cols-2">

    {{-- form side --}}
    <div class="flex flex-col justify-center px-6 sm:px-12 lg:px-16 py-12 relative">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 mb-10 group w-fit">
            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 via-violet-500 to-cyan-400 grid place-items-center shadow-lg shadow-brand-500/30 group-hover:scale-105 transition-transform">
                <x-icon name="layers" class="w-5 h-5 text-white" />
            </span>
            <span class="font-bold text-lg tracking-tight">{{ $brand['name'] ?? config('platform.name') }}</span>
        </a>

        <div class="w-full max-w-md animate-[fade-up_.5s_cubic-bezier(.16,1,.3,1)_both]">
            {{ $slot ?? '' }}
            @yield('content')
        </div>

        <button class="absolute top-6 right-6 btn btn-ghost btn-icon" x-data x-on:click="$store.ui.cycleTheme()" aria-label="Toggle theme">
            <x-icon name="sun" class="w-[18px] h-[18px] dark:hidden" />
            <x-icon name="moon" class="w-[18px] h-[18px] hidden dark:block" />
        </button>
    </div>

    {{-- brand side --}}
    <div class="hidden lg:flex relative mesh-bg bg-ink-950 text-white overflow-hidden items-center justify-center p-16">
        <div class="relative z-10 max-w-lg">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 ring-1 ring-white/15 text-xs font-semibold mb-7 backdrop-blur">
                <x-icon name="sparkles" class="w-3.5 h-3.5" /> AI-powered website builder
            </div>
            <h2 class="text-4xl xl:text-5xl font-extrabold tracking-tight leading-[1.08]">
                Describe your idea.<br>
                <span class="bg-gradient-to-r from-brand-300 via-violet-300 to-cyan-300 bg-clip-text text-transparent">Get a website in seconds.</span>
            </h2>
            <p class="text-white/65 mt-5 text-lg leading-relaxed">
                A complete drag-and-drop studio with AI generation, responsive controls, custom domains
                and one-click publishing.
            </p>

            <div class="grid grid-cols-2 gap-3 mt-10">
                @foreach([
                    ['sparkles', 'AI generation', 'Full sites in one prompt'],
                    ['cursor', 'Drag & drop', 'Visual, no-code editing'],
                    ['devices', 'Responsive', 'Perfect on every screen'],
                    ['rocket', 'One-click publish', 'Live in under a minute'],
                ] as [$icon, $title, $desc])
                    <div class="rounded-2xl bg-white/[.06] ring-1 ring-white/10 p-4 backdrop-blur">
                        <x-icon :name="$icon" class="w-5 h-5 text-cyan-300 mb-2.5" />
                        <p class="font-semibold text-sm">{{ $title }}</p>
                        <p class="text-xs text-white/55 mt-0.5">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center gap-4 mt-10 pt-8 border-t border-white/10">
                <div class="flex -space-x-2.5">
                    @foreach(['A','J','M','S'] as $i => $l)
                        <span class="w-9 h-9 rounded-full grid place-items-center text-xs font-bold ring-2 ring-ink-950"
                              style="background-image:linear-gradient(135deg,hsl({{ $i*70 }} 70% 58%),hsl({{ $i*70+40 }} 70% 48%))">{{ $l }}</span>
                    @endforeach
                </div>
                <div class="text-sm">
                    <p class="font-semibold">Trusted by 48,000+ builders</p>
                    <p class="text-white/50 text-xs flex items-center gap-1 mt-0.5">
                        @for($i = 0; $i < 5; $i++)<x-icon name="star" class="w-3 h-3 fill-amber-400 text-amber-400" />@endfor
                        <span class="ml-1">4.9 average rating</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@livewireScripts
</body>
</html>
