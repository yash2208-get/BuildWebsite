<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Down for maintenance</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen grid place-items-center p-6 mesh-bg bg-ink-950 text-white">
    <div class="text-center max-w-md">
        <span class="w-16 h-16 rounded-2xl bg-white/10 ring-1 ring-white/15 grid place-items-center mx-auto mb-6 backdrop-blur">
            <x-icon name="wrench" class="w-8 h-8 text-cyan-300" />
        </span>
        <h1 class="text-3xl font-extrabold tracking-tight">We'll be right back</h1>
        <p class="text-white/60 mt-4 leading-relaxed">{{ $message ?? 'We are performing scheduled maintenance and will return shortly.' }}</p>
    </div>
</body>
</html>
