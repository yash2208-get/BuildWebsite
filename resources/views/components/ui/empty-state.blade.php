@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'description' => null, 'compact' => false])
<div {{ $attributes->class(['text-center', $compact ? 'py-10 px-6' : 'py-16 px-6']) }}>
    <div class="relative mx-auto {{ $compact ? 'w-14 h-14' : 'w-20 h-20' }} mb-5">
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-brand-500/18 to-cyan-400/12 blur-xl"></div>
        <div class="relative w-full h-full rounded-2xl surface grid place-items-center">
            <x-icon :name="$icon" class="{{ $compact ? 'w-6 h-6' : 'w-8 h-8' }} text-brand-500" />
        </div>
    </div>
    <h3 class="font-semibold tracking-tight {{ $compact ? 'text-sm' : 'text-base' }}">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-tertiary mt-1.5 max-w-sm mx-auto leading-relaxed">{{ $description }}</p>
    @endif
    @if(trim($slot) !== '')
        <div class="mt-6 flex items-center justify-center gap-3 flex-wrap">{{ $slot }}</div>
    @endif
</div>
