<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Settings extends BaseComponent
{
    use WithFileUploads;

    #[Url(except: 'profile')]
    public string $tab = 'profile';

    public string $name = '';
    public string $email = '';
    public string $username = '';
    public string $bio = '';
    public string $company = '';
    public string $website = '';
    public string $timezone = 'UTC';
    public $avatar = null;

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public bool $emailMarketing = true;
    public bool $emailProduct = true;
    public bool $emailSecurity = true;
    public bool $emailWeekly = false;

    public string $keyName = '';
    public ?string $newKeyPlain = null;

    public bool $confirmingDeletion = false;
    public string $deleteConfirmation = '';

    public function mount(): void
    {
        $user = $this->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = (string) $user->username;
        $this->bio = (string) $user->bio;

        $meta = $user->meta ?? [];
        $this->company = $meta['company'] ?? '';
        $this->website = $meta['website'] ?? '';
        $this->timezone = $meta['timezone'] ?? 'UTC';

        $prefs = $user->preferences ?? [];
        $this->emailMarketing = $prefs['email_marketing'] ?? true;
        $this->emailProduct = $prefs['email_product'] ?? true;
        $this->emailSecurity = $prefs['email_security'] ?? true;
        $this->emailWeekly = $prefs['email_weekly'] ?? false;
    }

    public function saveProfile(): void
    {
        $user = $this->user();

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'username' => ['nullable', 'string', 'alpha_dash', 'max:60', 'unique:users,username,'.$user->id],
            'bio' => ['nullable', 'string', 'max:500'],
            'company' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url', 'max:190'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?: null,
            'bio' => $data['bio'],
            'meta' => array_merge($user->meta ?? [], [
                'company' => $data['company'],
                'website' => $data['website'],
                'timezone' => $this->timezone,
            ]),
        ];

        if ($this->avatar) {
            $attributes['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $user->update($attributes);

        $this->reset('avatar');
        ActivityLog::record('updated', 'Profile details updated', $user);
        $this->notifySuccess('Profile updated.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'confirmed:newPasswordConfirmation', Password::defaults()],
        ], [
            'currentPassword.current_password' => 'That is not your current password.',
        ]);

        $this->user()->update(['password' => Hash::make($this->newPassword)]);

        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');

        SecurityLog::record('password_changed', ['email' => $this->user()->email], 'success');
        $this->notifySuccess('Password updated successfully.');
    }

    public function saveNotifications(): void
    {
        $this->user()->update([
            'preferences' => array_merge($this->user()->preferences ?? [], [
                'email_marketing' => $this->emailMarketing,
                'email_product' => $this->emailProduct,
                'email_security' => $this->emailSecurity,
                'email_weekly' => $this->emailWeekly,
            ]),
        ]);

        $this->notifySuccess('Notification preferences saved.');
    }

    public function createApiKey(): void
    {
        $this->validate(['keyName' => ['required', 'string', 'min:2', 'max:80']]);

        [$key, $plain] = ApiKey::generate([
            'user_id' => $this->user()->id,
            'name' => $this->keyName,
            'abilities' => ['websites:read', 'websites:write'],
            'rate_limit' => 60,
        ]);

        $this->newKeyPlain = $plain;
        $this->reset('keyName');
        $this->notifySuccess('API key created — copy it now, it will not be shown again.');
    }

    public function revokeApiKey(int $id): void
    {
        ApiKey::where('user_id', $this->user()->id)->findOrFail($id)->delete();
        $this->notifySuccess('API key revoked.');
    }

    public function deleteAccount(): void
    {
        $this->validate([
            'deleteConfirmation' => ['required', 'in:DELETE'],
        ], ['deleteConfirmation.in' => 'Type DELETE exactly to confirm.']);

        $user = $this->user();

        SecurityLog::record('account_deleted', ['email' => $user->email], 'warning');

        Auth::logout();
        $user->delete();

        $this->redirectRoute('home', navigate: false);
    }

    public function render()
    {
        return view('livewire.app.settings', [
            'apiKeys' => ApiKey::where('user_id', $this->user()->id)->latest()->get(),
            'sessions' => SecurityLog::where('event', 'login_success')
                ->where('user_id', $this->user()->id)
                ->latest()->limit(6)->get(),
            'timezones' => ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Singapore', 'Asia/Tokyo', 'Australia/Sydney'],
        ])->layoutData($this->layoutData('Account Settings'));
    }
}
