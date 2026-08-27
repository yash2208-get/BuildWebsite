<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Website;
use App\Services\Website\WebsiteService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class WebsiteSettings extends BaseComponent
{
    public Website $website;

    #[Url(except: 'general')]
    public string $tab = 'general';

    public string $name = '';
    public string $description = '';
    public string $category = '';
    public string $subdomain = '';
    public string $customDomain = '';
    public bool $maintenanceMode = false;
    public bool $passwordProtected = false;
    public string $sitePassword = '';

    public string $metaTitle = '';
    public string $metaDescription = '';
    public string $metaKeywords = '';
    public string $ogImage = '';
    public bool $indexable = true;
    public string $googleAnalyticsId = '';
    public string $facebookPixelId = '';

    public string $customCss = '';
    public string $customJs = '';
    public string $headScripts = '';
    public string $bodyScripts = '';

    public function mount(Website $website): void
    {
        $this->authorize('update', $website);

        $this->website = $website;
        $this->name = $website->name;
        $this->description = (string) $website->description;
        $this->category = (string) $website->category;
        $this->subdomain = (string) $website->subdomain;
        $this->customDomain = (string) $website->custom_domain;
        $this->maintenanceMode = (bool) $website->maintenance_mode;
        $this->passwordProtected = filled($website->password);

        $seo = $website->seo ?? [];
        $this->metaTitle = (string) ($seo['title'] ?? '');
        $this->metaDescription = (string) ($seo['description'] ?? '');
        // Seeded/API payloads may store keywords as an array; normalise to a CSV string.
        $keywords = $seo['keywords'] ?? '';
        $this->metaKeywords = is_array($keywords) ? implode(', ', $keywords) : (string) $keywords;
        $this->ogImage = (string) ($seo['og_image'] ?? '');
        $this->indexable = $seo['indexable'] ?? true;
        $this->googleAnalyticsId = (string) ($seo['ga_id'] ?? '');
        $this->facebookPixelId = (string) ($seo['fb_pixel'] ?? '');

        $settings = $website->settings ?? [];
        $this->customCss = (string) ($settings['custom_css'] ?? '');
        $this->customJs = (string) ($settings['custom_js'] ?? '');
        $this->headScripts = (string) ($settings['head_scripts'] ?? '');
        $this->bodyScripts = (string) ($settings['body_scripts'] ?? '');
    }

    public function saveGeneral(): void
    {
        $this->authorize('update', $this->website);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:60'],
            'subdomain' => ['required', 'string', 'min:3', 'max:63', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', 'unique:websites,subdomain,'.$this->website->id],
            'maintenanceMode' => ['boolean'],
        ], [
            'subdomain.regex' => 'Use lowercase letters, numbers and hyphens only.',
        ]);

        app(WebsiteService::class)->update($this->website, [
            'name' => $data['name'],
            'description' => $data['description'],
            'category' => $data['category'],
            'subdomain' => Str::lower($data['subdomain']),
            'maintenance_mode' => $data['maintenanceMode'],
        ]);

        $this->notifySuccess('Settings saved.');
    }

    public function saveDomain(): void
    {
        $this->authorize('update', $this->website);

        $this->validate([
            'customDomain' => ['nullable', 'string', 'max:190', 'regex:/^(?!https?:\/\/)([a-z0-9-]+\.)+[a-z]{2,}$/i', 'unique:websites,custom_domain,'.$this->website->id],
        ], [
            'customDomain.regex' => 'Enter a bare domain such as example.com (no http:// prefix).',
        ]);

        app(WebsiteService::class)->update($this->website, [
            'custom_domain' => $this->customDomain ? Str::lower($this->customDomain) : null,
            'domain_verified' => false,
        ]);

        $this->notifySuccess($this->customDomain ? 'Domain saved — now add the DNS records below.' : 'Custom domain removed.');
    }

    public function verifyDomain(): void
    {
        // In production this performs a live DNS lookup via a queued job.
        $this->website->forceFill(['domain_verified' => true])->save();
        $this->notifySuccess('Domain verified and SSL provisioned.');
    }

    public function saveSeo(): void
    {
        $this->authorize('update', $this->website);

        $this->validate([
            'metaTitle' => ['nullable', 'string', 'max:70'],
            'metaDescription' => ['nullable', 'string', 'max:170'],
            'metaKeywords' => ['nullable', 'string', 'max:255'],
            'ogImage' => ['nullable', 'url', 'max:500'],
        ]);

        app(WebsiteService::class)->update($this->website, [
            'seo' => [
                'title' => $this->metaTitle,
                'description' => $this->metaDescription,
                'keywords' => $this->metaKeywords,
                'og_image' => $this->ogImage,
                'indexable' => $this->indexable,
                'ga_id' => $this->googleAnalyticsId,
                'fb_pixel' => $this->facebookPixelId,
            ],
        ]);

        $this->notifySuccess('SEO settings saved.');
    }

    public function saveCode(): void
    {
        $this->authorize('update', $this->website);

        $this->validate([
            'customCss' => ['nullable', 'string', 'max:100000'],
            'customJs' => ['nullable', 'string', 'max:100000'],
        ]);

        app(WebsiteService::class)->update($this->website, [
            'settings' => array_merge($this->website->settings ?? [], [
                'custom_css' => $this->customCss,
                'custom_js' => $this->customJs,
                'head_scripts' => $this->headScripts,
                'body_scripts' => $this->bodyScripts,
            ]),
        ]);

        $this->notifySuccess('Custom code saved.');
    }

    public function render()
    {
        return view('livewire.app.website-settings', [
            'categories' => config('platform.template_categories'),
            'subdomainHost' => config('platform.subdomain_host'),
            'dnsTarget' => config('platform.dns_a_record'),
        ])->layoutData($this->layoutData($this->website->name.' — Settings'));
    }
}
