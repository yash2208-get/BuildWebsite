<x-layouts.guest title="Sign in">
    <h1 class="text-3xl font-extrabold tracking-tight">Welcome back</h1>
    <p class="text-secondary mt-2">Sign in to continue building.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="label" for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="field @error('email') field-error @enderror" placeholder="you@company.com">
            @error('email')<p class="error-text">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="label mb-0" for="password">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">Forgot password?</a>
            </div>
            <div x-data="{ show: false }" class="relative">
                <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                       class="field pr-10 @error('password') field-error @enderror" placeholder="••••••••">
                <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-tertiary hover:text-secondary" aria-label="Toggle password">
                    <x-icon name="eye" class="w-4 h-4" />
                </button>
            </div>
            @error('password')<p class="error-text">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2.5 text-sm cursor-pointer select-none">
            <input type="checkbox" name="remember" class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4">
            <span class="text-secondary">Keep me signed in</span>
        </label>

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Sign in <x-icon name="arrow-right" class="w-4 h-4" />
        </button>
    </form>

    <p class="text-sm text-secondary mt-6 text-center">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Create one free</a>
    </p>

    {{-- demo accounts --}}
    <div class="mt-8 rounded-2xl surface p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-tertiary mb-3">Demo accounts — password: <code class="font-mono text-brand-500">password</code></p>
        <div class="space-y-1.5">
            @foreach([
                ['super@aurorabuild.test', 'Super Admin', 'fuchsia'],
                ['admin@aurorabuild.test', 'Admin', 'sky'],
                ['user@aurorabuild.test', 'User', 'emerald'],
            ] as [$email, $label, $color])
                <button type="button" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-surface-muted transition-colors text-left group"
                        onclick="document.getElementById('email').value='{{ $email }}';document.getElementById('password').value='password';">
                    <x-ui.badge :color="$color">{{ $label }}</x-ui.badge>
                    <span class="text-xs font-mono text-tertiary truncate">{{ $email }}</span>
                    <span class="ml-auto text-[10px] font-semibold text-brand-500 opacity-0 group-hover:opacity-100 transition-opacity">Use →</span>
                </button>
            @endforeach
        </div>
    </div>
</x-layouts.guest>
