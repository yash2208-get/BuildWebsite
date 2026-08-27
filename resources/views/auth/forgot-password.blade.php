<x-layouts.guest title="Reset password">
    <h1 class="text-3xl font-extrabold tracking-tight">Reset your password</h1>
    <p class="text-secondary mt-2">Enter your email and we'll send a reset link.</p>

    @if(session('status'))
        <div class="mt-6 rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="label" for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="field @error('email') field-error @enderror" placeholder="you@company.com">
            @error('email')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-full">Send reset link</button>
    </form>

    <p class="text-sm text-secondary mt-6 text-center">
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">← Back to sign in</a>
    </p>
</x-layouts.guest>
