<div class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2.5 pointer-events-none" x-data>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-x-8"
             class="pointer-events-auto card shadow-xl px-4 py-3 flex items-start gap-3 min-w-[280px] max-w-sm"
             :class="{
                'ring-1 ring-emerald-500/30': toast.type === 'success',
                'ring-1 ring-rose-500/30': toast.type === 'error',
                'ring-1 ring-amber-500/30': toast.type === 'warning',
             }">
            <span class="w-7 h-7 rounded-lg grid place-items-center shrink-0 mt-0.5"
                  :class="{
                    'bg-emerald-500/15 text-emerald-600': toast.type === 'success',
                    'bg-rose-500/15 text-rose-600': toast.type === 'error',
                    'bg-amber-500/15 text-amber-600': toast.type === 'warning',
                    'bg-brand-500/15 text-brand-600': toast.type === 'info',
                  }">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path x-show="toast.type === 'success'" d="M4.5 12.5l5 5 10-11" />
                    <path x-show="toast.type === 'error'" d="M6 6l12 12M18 6L6 18" />
                    <path x-show="toast.type === 'warning'" d="M12 8v5M12 17h.01M10.3 3.9L2.6 17.4A2 2 0 0 0 4.3 20.5h15.4a2 2 0 0 0 1.7-3.1L13.7 3.9a2 2 0 0 0-3.4 0z" />
                    <path x-show="toast.type === 'info'" d="M12 8h.01M11 12h1v5h1" />
                </svg>
            </span>
            <p class="text-sm leading-snug pt-1" x-text="toast.message"></p>
            <button class="ml-auto text-tertiary hover:text-current transition-colors shrink-0" x-on:click="$store.toasts.remove(toast.id)" aria-label="Dismiss">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    </template>
</div>

@if(session('success') || session('error') || session('warning') || session('status'))
    <div x-data x-init="$store.toasts.push(@js(session('success') ?? session('error') ?? session('warning') ?? session('status')), @js(session('error') ? 'error' : (session('warning') ? 'warning' : 'success')))"></div>
@endif
