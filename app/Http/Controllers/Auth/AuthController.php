<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SecurityLog;
use App\Models\User;
use App\Services\Platform\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            SecurityLog::record('login_failed', ['email' => $credentials['email']], 'warning');

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = $request->user();

        if ($user->isSuspended()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        SecurityLog::record('login_success', ['email' => $user->email], 'success');
        ActivityLog::record('login', "{$user->name} signed in", $user);

        return redirect()->intended(route($user->homeRoute()));
    }

    public function showRegister(): View
    {
        abort_unless(app(SettingsService::class)->get('allow_registration', true), 403, 'Registration is currently closed.');

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        abort_unless(app(SettingsService::class)->get('allow_registration', true), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $user->assignRole(RoleType::User->value);

        Auth::login($user);
        $request->session()->regenerate();

        SecurityLog::record('registered', ['email' => $user->email], 'success');
        ActivityLog::record('created', "New account registered: {$user->email}", $user);

        return redirect()->route('app.dashboard')
            ->with('success', 'Welcome aboard! Your workspace is ready.');
    }

    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        SecurityLog::record('password_reset_requested', ['email' => $request->string('email')->toString()]);

        // Always respond identically to avoid leaking which addresses exist.
        return back()->with('status', 'If an account exists for that address, a reset link is on its way.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            SecurityLog::record('logout', ['email' => $user->email]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
