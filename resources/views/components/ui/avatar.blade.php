@props(['user' => null, 'name' => null, 'src' => null, 'size' => 'md'])
@php
    $sizes = ['xs' => 'w-6 h-6 text-[10px]', 'sm' => 'w-8 h-8 text-xs', 'md' => 'w-10 h-10 text-sm', 'lg' => 'w-14 h-14 text-lg', 'xl' => 'w-20 h-20 text-2xl'];
    $cls = $sizes[$size] ?? $sizes['md'];
    $label = $name ?? $user?->name ?? '?';
    $img = $src ?? $user?->avatar_url;
    $initials = collect(preg_split('/\s+/', trim($label)))->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    $hue = crc32($label) % 360;
@endphp
@if($img)
    <img src="{{ $img }}" alt="{{ $label }}" {{ $attributes->class(["$cls rounded-full object-cover ring-2 ring-white/70 dark:ring-white/10"]) }}>
@else
    <span {{ $attributes->class(["$cls rounded-full grid place-items-center font-semibold text-white shrink-0 ring-2 ring-white/70 dark:ring-white/10"]) }}
          style="background-image:linear-gradient(135deg, hsl({{ $hue }} 72% 58%), hsl({{ ($hue + 45) % 360 }} 74% 48%))">{{ $initials }}</span>
@endif
