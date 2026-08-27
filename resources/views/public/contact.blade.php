@extends('layouts.public')
@section('meta_title', 'Contact us')

@section('content')
<section class="max-w-7xl mx-auto px-5 sm:px-8 pt-20 pb-20">
    <div class="grid lg:grid-cols-2 gap-14">
        <div>
            <x-ui.badge color="brand">Contact</x-ui.badge>
            <h1 class="text-5xl font-extrabold tracking-tight mt-5 leading-[1.05]">Let's talk</h1>
            <p class="text-lg text-secondary mt-5 leading-relaxed">
                Questions about plans, migrations or enterprise features? Send us a note and we will
                get back to you within one business day.
            </p>

            <div class="space-y-4 mt-10">
                @foreach([
                    ['mail', 'Email us', $brand['support_email'] ?? config('platform.support_email'), 'General enquiries and support'],
                    ['message', 'Live chat', 'Available 9am–6pm UTC', 'Fastest way to reach the team'],
                    ['book', 'Documentation', 'Guides and API reference', 'Self-serve answers, any time'],
                ] as [$icon, $title, $value, $desc])
                    <div class="card p-5 flex items-start gap-4">
                        <span class="w-10 h-10 rounded-xl bg-brand-500/12 text-brand-500 grid place-items-center shrink-0">
                            <x-icon :name="$icon" class="w-5 h-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm">{{ $title }}</p>
                            <p class="text-sm text-brand-600 dark:text-brand-400 mt-0.5 truncate">{{ $value }}</p>
                            <p class="text-xs text-tertiary mt-0.5">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-7">
            @if(session('success'))
                <div class="rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/20 px-4 py-3 mb-6 flex items-start gap-3">
                    <x-icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                    <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
                </div>
            @endif

            <h2 class="text-xl font-bold">Send us a message</h2>
            <p class="text-sm text-tertiary mt-1">We read every one.</p>

            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-4 mt-6">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label" for="name">Your name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="field @error('name') field-error @enderror" placeholder="Jane Cooper">
                        @error('name')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="field @error('email') field-error @enderror" placeholder="jane@company.com">
                        @error('email')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="label" for="phone">Phone <span class="text-tertiary font-normal">(optional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="field" placeholder="+1 555 000 0000">
                </div>
                <div>
                    <label class="label" for="subject">Subject</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="field" placeholder="How can we help?">
                </div>
                <div>
                    <label class="label" for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required class="field @error('message') field-error @enderror" placeholder="Tell us a bit about what you need…">{{ old('message') }}</textarea>
                    @error('message')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-full">
                    Send message <x-icon name="arrow-right" class="w-4 h-4" />
                </button>
                <p class="text-xs text-tertiary text-center">
                    By submitting you agree to our
                    <a href="{{ route('page.show','privacy') }}" class="text-brand-600 dark:text-brand-400 hover:underline">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </div>
</section>
@endsection
