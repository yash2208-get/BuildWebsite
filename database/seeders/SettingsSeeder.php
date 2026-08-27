<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // general
            ['general', 'platform_name', 'AuroraBuild', 'string', true, 'Platform name'],
            ['general', 'platform_tagline', 'The AI website builder for modern teams', 'string', true, 'Tagline'],
            ['general', 'support_email', 'support@aurorabuild.app', 'string', true, 'Support email'],
            ['general', 'maintenance_mode', '0', 'bool', false, 'Maintenance mode'],
            ['general', 'maintenance_message', 'We are performing scheduled maintenance and will be back shortly.', 'string', true, 'Maintenance message'],
            ['general', 'allow_registration', '1', 'bool', true, 'Allow public registration'],
            ['general', 'default_timezone', 'UTC', 'string', false, 'Default timezone'],

            // branding
            ['branding', 'brand_primary', '#6366f1', 'string', true, 'Primary colour'],
            ['branding', 'brand_accent', '#22d3ee', 'string', true, 'Accent colour'],
            ['branding', 'platform_logo', '', 'string', true, 'Logo URL'],
            ['branding', 'platform_favicon', '', 'string', true, 'Favicon URL'],

            // seo
            ['seo', 'seo_title', 'AuroraBuild — Build stunning websites with AI', 'string', true, 'Default meta title'],
            ['seo', 'seo_description', 'Design, generate and publish beautiful websites in minutes with an AI-powered drag & drop builder.', 'string', true, 'Default meta description'],
            ['seo', 'seo_keywords', 'website builder, ai website, no-code, landing pages', 'string', true, 'Default keywords'],
            ['seo', 'google_analytics_id', '', 'string', false, 'Google Analytics ID'],
            ['seo', 'robots_txt', "User-agent: *\nAllow: /", 'string', false, 'robots.txt'],

            // mail
            ['mail', 'mail_mailer', 'log', 'string', false, 'Mailer'],
            ['mail', 'mail_host', 'smtp.mailgun.org', 'string', false, 'SMTP host'],
            ['mail', 'mail_port', '587', 'int', false, 'SMTP port'],
            ['mail', 'mail_username', '', 'string', false, 'SMTP username'],
            ['mail', 'mail_encryption', 'tls', 'string', false, 'Encryption'],
            ['mail', 'mail_from_address', 'hello@aurorabuild.app', 'string', false, 'From address'],
            ['mail', 'mail_from_name', 'AuroraBuild', 'string', false, 'From name'],

            // storage
            ['storage', 'storage_driver', 'public', 'string', false, 'Storage driver'],
            ['storage', 'max_upload_mb', '8', 'int', false, 'Max upload size (MB)'],
            ['storage', 's3_bucket', '', 'string', false, 'S3 bucket'],
            ['storage', 's3_region', 'us-east-1', 'string', false, 'S3 region'],

            // payments
            ['payments', 'currency', 'USD', 'string', true, 'Currency'],
            ['payments', 'stripe_enabled', '0', 'bool', false, 'Enable Stripe'],
            ['payments', 'paypal_enabled', '0', 'bool', false, 'Enable PayPal'],
            ['payments', 'tax_percentage', '0', 'int', false, 'Tax percentage'],

            // domains
            ['domains', 'subdomain_host', 'aurorabuild.app', 'string', true, 'Subdomain host'],
            ['domains', 'custom_domains_enabled', '1', 'bool', false, 'Allow custom domains'],
            ['domains', 'dns_target_ip', '203.0.113.10', 'string', true, 'DNS A record target'],

            // ai
            ['ai', 'ai_driver', 'simulated', 'string', false, 'AI driver'],
            ['ai', 'ai_enabled', '1', 'bool', false, 'Enable AI features'],
            ['ai', 'ai_monthly_free_credits', '25', 'int', false, 'Free monthly credits'],

            // security
            ['security', 'force_two_factor_admins', '0', 'bool', false, 'Force 2FA for admins'],
            ['security', 'max_login_attempts', '5', 'int', false, 'Max login attempts'],
            ['security', 'session_lifetime', '120', 'int', false, 'Session lifetime (minutes)'],
        ];

        foreach ($settings as [$group, $key, $value, $type, $public, $label]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => $group, 'value' => $value, 'type' => $type, 'is_public' => $public, 'label' => $label],
            );
        }
    }
}
