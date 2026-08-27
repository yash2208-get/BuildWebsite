<div class="relative hidden md:block w-full max-w-sm" x-data="{ q: '' }">
    <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
    <input type="search" x-model="q" placeholder="Search…"
           class="field pl-9 pr-16 py-2 text-sm"
           x-on:keydown.window.prevent.cmd.k="$el.focus()"
           x-on:keydown.window.prevent.ctrl.k="$el.focus()">
    <kbd class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-tertiary bg-surface-muted px-1.5 py-0.5 rounded border border-subtle pointer-events-none">⌘K</kbd>
</div>
