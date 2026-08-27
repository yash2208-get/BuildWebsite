<x-layouts.guest title="Create account">
    <h1 class="text-3xl font-extrabold tracking-tight">Create your account</h1>
    <p class="text-secondary mt-2">Start building for free — no card required.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="label" for="name">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   class="field @error('name') field-error @enderror" placeholder="Jane Cooper">
            @error('name')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="field @error('email') field-error @enderror" placeholder="you@company.com">
            @error('email')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="field @error('password') field-error @enderror" placeholder="At least 8 characters">
            @error('password')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="field" placeholder="Repeat your password">
        </div>

        <label class="flex items-start gap-2.5 text-sm cursor-pointer select-none">
            <input type="checkbox" name="terms" required class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4 mt-0.5">
            <span class="text-secondary">I agree to the
                <a href="{{ route('page.show','terms') }}" class="text-brand-600 dark:text-brand-400 hover:underline">Terms</a> and
                <a href="{{ route('page.show','privacy') }}" class="text-brand-600 dark:text-brand-400 hover:underline">Privacy Policy</a>
            </span>
        </label>
        @error('terms')<p class="error-text">{{ $message }}</p>@enderror

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Create free account <x-icon name="arrow-right" class="w-4 h-4" />
        </button>
    </form>

    <p class="text-sm text-secondary mt-6 text-center">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Sign in</a>
    </p>
</x-layouts.guest>
