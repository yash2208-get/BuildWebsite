@php $u = auth()->user(); @endphp
<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
    <button class="flex items-center gap-2 rounded-xl p-1 pr-2 hover:bg-surface-muted transition-colors" x-on:click="open = !open">
        <x-ui.avatar :user="$u" size="sm" />
        <span class="hidden sm:block text-left min-w-0">
            <span class="block text-xs font-semibold truncate max-w-[120px]">{{ $u?->name }}</span>
            <span class="block text-[10px] text-tertiary truncate max-w-[120px]">{{ $u?->email }}</span>
        </span>
        <x-icon name="chevron-down" class="w-3.5 h-3.5 text-tertiary hidden sm:block" />
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.right
         class="absolute right-0 mt-2 w-60 card shadow-xl overflow-hidden z-50 py-1.5">
        <div class="px-4 py-3 border-b border-subtle">
            <p class="text-sm font-semibold truncate">{{ $u?->name }}</p>
            <p class="text-xs text-tertiary truncate">{{ $u?->email }}</p>
            <x-ui.badge :color="$u?->primaryRole()->color() ?? 'slate'" class="mt-2">{{ $u?->primaryRole()->label() }}</x-ui.badge>
        </div>
        <div class="py-1">
            <a href="{{ route('app.settings') }}" class="nav-link rounded-none px-4"><x-icon name="settings" class="w-4 h-4" />Account settings</a>
            @customer
                <a href="{{ route('app.billing') }}" class="nav-link rounded-none px-4"><x-icon name="credit-card" class="w-4 h-4" />Billing & plan</a>
            @endcustomer
            <a href="{{ route('support.index') }}" class="nav-link rounded-none px-4"><x-icon name="help" class="w-4 h-4" />Help & support</a>
        </div>
        <div class="border-t border-subtle pt-1">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link rounded-none px-4 w-full text-rose-600 dark:text-rose-400 hover:bg-rose-500/10">
                    <x-icon name="logout" class="w-4 h-4" />Sign out
                </button>
            </form>
        </div>
    </div>
</div>
