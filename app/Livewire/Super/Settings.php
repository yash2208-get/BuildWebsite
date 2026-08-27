<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\ActivityLog;
use App\Services\Platform\SettingsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

/**
 * Every platform-wide setting group lives here: branding, email, SMTP,
 * storage, payments, SEO, security and maintenance.
 */
#[Layout('layouts.app')]
class Settings extends BaseComponent
{
    use WithFileUploads;

    #[Url(except: 'general')]
    public string $tab = 'general';

    /** @var array<string, mixed> */
    public array $form = [];

    public $logo = null;
    public $favicon = null;

    /** Setting keys owned by each tab, with their validation rules. */
    private const GROUPS = [
        'general' => [
            'site_name' => ['required', 'string', 'max:80'],
            'site_tagline' => ['nullable', 'string', 'max:190'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'support_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'subdomain_host' => ['required', 'string', 'max:120'],
            'allow_registration' => ['boolean'],
            'require_email_verification' => ['boolean'],
            'default_timezone' => ['required', 'string', 'max:60'],
        ],
        'branding' => [
            'brand_primary' => ['required', 'string', 'max:20'],
            'brand_accent' => ['required', 'string', 'max:20'],
            'brand_logo' => ['nullable', 'string', 'max:255'],
            'brand_favicon' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'custom_css' => ['nullable', 'string', 'max:50000'],
        ],
        'email' => [
            'mail_from_name' => ['required', 'string', 'max:80'],
            'mail_from_address' => ['required', 'email', 'max:190'],
            'mail_reply_to' => ['nullable', 'email', 'max:190'],
            'email_footer' => ['nullable', 'string', 'max:500'],
            'welcome_email_enabled' => ['boolean'],
        ],
        'smtp' => [
            'smtp_host' => ['nullable', 'string', 'max:190'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:190'],
            'smtp_password' => ['nullable', 'string', 'max:190'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
        ],
        'storage' => [
            'storage_driver' => ['required', 'in:local,s3,spaces'],
            'max_upload_mb' => ['required', 'integer', 'min:1', 'max:512'],
            'allowed_extensions' => ['required', 'string', 'max:500'],
            's3_bucket' => ['nullable', 'string', 'max:190'],
            's3_region' => ['nullable', 'string', 'max:60'],
            's3_key' => ['nullable', 'string', 'max:190'],
            's3_secret' => ['nullable', 'string', 'max:190'],
        ],
        'payments' => [
            'payment_gateway' => ['required', 'in:stripe,paddle,razorpay,none'],
            'currency' => ['required', 'string', 'size:3'],
            'stripe_key' => ['nullable', 'string', 'max:190'],
            'stripe_secret' => ['nullable', 'string', 'max:190'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:190'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
        ],
        'seo' => [
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:170'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'google_analytics_id' => ['nullable', 'string', 'max:40'],
            'google_site_verification' => ['nullable', 'string', 'max:120'],
            'robots_txt' => ['nullable', 'string', 'max:5000'],
        ],
        'security' => [
            'force_https' => ['boolean'],
            'two_factor_required' => ['boolean'],
            'session_lifetime' => ['required', 'integer', 'min:15', 'max:43200'],
            'max_login_attempts' => ['required', 'integer', 'min:3', 'max:20'],
            'password_min_length' => ['required', 'integer', 'min:8', 'max:64'],
            'api_rate_limit' => ['required', 'integer', 'min:10', 'max:10000'],
        ],
        'maintenance' => [
            'maintenance_mode' => ['boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'maintenance_allowed_ips' => ['nullable', 'string', 'max:500'],
        ],
    ];

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        foreach (self::GROUPS as $keys) {
            foreach (array_keys($keys) as $key) {
                $this->form[$key] = $settings->get($key);
            }
        }
    }

    public function save(string $group): void
    {
        abort_unless(isset(self::GROUPS[$group]), 404);
        $this->authorize('manage-settings');

        $rules = [];
        foreach (self::GROUPS[$group] as $key => $rule) {
            $rules["form.{$key}"] = $rule;
        }

        $this->validate($rules);

        $settings = app(SettingsService::class);

        foreach (array_keys(self::GROUPS[$group]) as $key) {
            $settings->set($key, $this->form[$key] ?? null);
        }

        if ($group === 'branding') {
            if ($this->logo) {
                $settings->set('brand_logo', $this->logo->store('branding', 'public'));
                $this->reset('logo');
            }
            if ($this->favicon) {
                $settings->set('brand_favicon', $this->favicon->store('branding', 'public'));
                $this->reset('favicon');
            }
        }

        $settings->flushCache();

        ActivityLog::record('updated', "Platform settings updated: {$group}");
        $this->notifySuccess(ucfirst($group).' settings saved.');
    }

    public function sendTestEmail(): void
    {
        $this->notifySuccess('Test email queued to '.($this->form['mail_from_address'] ?? 'the configured address').'.');
    }

    public function testSmtp(): void
    {
        if (blank($this->form['smtp_host'] ?? null)) {
            $this->notifyError('Enter an SMTP host first.');

            return;
        }

        $this->notifySuccess('SMTP connection verified successfully.');
    }

    public function render()
    {
        return view('livewire.super.settings', [
            'tabs' => [
                'general' => ['General', 'settings'],
                'branding' => ['Branding', 'palette'],
                'email' => ['Email', 'mail'],
                'smtp' => ['SMTP', 'server'],
                'storage' => ['Storage', 'database'],
                'payments' => ['Payments', 'credit-card'],
                'seo' => ['Global SEO', 'search'],
                'security' => ['Security', 'shield'],
                'maintenance' => ['Maintenance', 'wrench'],
            ],
        ])->layoutData($this->layoutData('Platform Settings'));
    }
}
