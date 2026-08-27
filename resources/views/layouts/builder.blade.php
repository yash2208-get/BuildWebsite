<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Builder' }} — {{ $brand['name'] ?? config('platform.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>(function(){const t=localStorage.getItem('aurora-theme')||'system';document.documentElement.classList.toggle('dark',t==='dark'||(t==='system'&&matchMedia('(prefers-color-scheme: dark)').matches));})();</script>
    @vite(['resources/css/app.css', 'resources/js/builder.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="h-full overflow-hidden bg-surface-muted">
    {{ $slot }}
    @include('partials.toasts')
    @livewireScripts
    @stack('scripts')
</body>
</html>
