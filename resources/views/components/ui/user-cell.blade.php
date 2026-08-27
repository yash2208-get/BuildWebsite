@props(['user', 'subtitle' => null])

@if($user)
    <div class="flex items-center gap-3 min-w-0">
        <x-ui.avatar :name="$user->name" :src="$user->avatar_url" size="sm" />
        <div class="min-w-0">
            <p class="font-medium truncate">{{ $user->name }}</p>
            <p class="text-xs text-tertiary truncate">{{ $subtitle ?? $user->email }}</p>
        </div>
    </div>
@else
    <span class="text-tertiary text-sm">—</span>
@endif
