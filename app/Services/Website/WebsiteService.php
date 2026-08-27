<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Enums\WebsiteStatus;
use App\Models\ActivityLog;
use App\Models\Page;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Eloquent\WebsiteRepository;
use App\Support\Exceptions\QuotaExceededException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebsiteService
{
    public function __construct(
        private readonly WebsiteRepository $websites,
        private readonly QuotaService $quotas,
    ) {}

    /**
     * Create a new website for a user, seeding a homepage.
     *
     * @throws QuotaExceededException
     */
    public function create(User $user, array $data): Website
    {
        $this->quotas->assertCanCreateWebsite($user);

        return DB::transaction(function () use ($user, $data) {
            $website = $this->websites->create([
                'user_id' => $user->id,
                'theme_id' => $data['theme_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'business',
                'subdomain' => $this->uniqueSubdomain($data['subdomain'] ?? $data['name']),
                'status' => WebsiteStatus::Draft->value,
                'settings' => $data['settings'] ?? $this->defaultSettings(),
                'seo' => $data['seo'] ?? [
                    'title' => $data['name'],
                    'description' => $data['description'] ?? '',
                    'keywords' => [],
                    'robots' => 'index,follow',
                ],
                'last_edited_at' => now(),
            ]);

            if (! ($data['skip_homepage'] ?? false)) {
                $this->createHomepage($website);
            }

            $user->increment('websites_count');

            return $website;
        });
    }

    /** Create a website from a template, copying every template page. */
    public function createFromTemplate(User $user, Template $template, array $data = []): Website
    {
        $this->quotas->assertCanCreateWebsite($user);

        return DB::transaction(function () use ($user, $template, $data) {
            $website = $this->create($user, array_merge([
                'name' => $data['name'] ?? $template->name,
                'category' => $template->category,
                'skip_homepage' => true,
            ], $data));

            $pages = $template->pages ?: [[
                'title' => 'Home',
                'slug' => 'home',
                'is_homepage' => true,
                'html' => $template->html,
                'css' => $template->css,
            ]];

            foreach (array_values($pages) as $i => $page) {
                Page::create([
                    'website_id' => $website->id,
                    'title' => $page['title'] ?? 'Page '.($i + 1),
                    'slug' => Str::slug($page['slug'] ?? $page['title'] ?? 'page-'.($i + 1)),
                    'html' => $page['html'] ?? '',
                    'css' => $page['css'] ?? $template->css,
                    'grapes_data' => $page['grapes_data'] ?? null,
                    'seo' => $page['seo'] ?? null,
                    'is_homepage' => (bool) ($page['is_homepage'] ?? $i === 0),
                    'sort_order' => $i,
                    'status' => 'draft',
                ]);
            }

            $website->update(['pages_count' => count($pages)]);
            $template->increment('uses_count');

            return $website->fresh();
        });
    }

    /** Deep-clone a website including all of its pages. */
    public function clone(Website $website, ?string $name = null): Website
    {
        $this->quotas->assertCanCreateWebsite($website->user);

        return DB::transaction(function () use ($website, $name) {
            $copy = $website->replicate([
                'subdomain', 'custom_domain', 'domain_status', 'domain_verification_token',
                'views_count', 'published_at', 'deleted_at',
            ]);

            $copy->name = $name ?: $website->name.' (Copy)';
            $copy->slug = null;
            $copy->subdomain = $this->uniqueSubdomain($copy->name);
            $copy->status = WebsiteStatus::Draft->value;
            $copy->views_count = 0;
            $copy->published_at = null;
            $copy->last_edited_at = now();
            $copy->save();

            foreach ($website->pages()->get() as $page) {
                $pageCopy = $page->replicate(['views_count', 'published_at', 'deleted_at']);
                $pageCopy->website_id = $copy->id;
                $pageCopy->views_count = 0;
                $pageCopy->status = 'draft';
                $pageCopy->published_at = null;
                $pageCopy->save();
            }

            $copy->update(['pages_count' => $copy->pages()->count()]);
            $website->user?->increment('websites_count');

            ActivityLog::record('cloned', "Website \"{$website->name}\" was cloned", $copy);

            return $copy;
        });
    }

    public function publish(Website $website): Website
    {
        DB::transaction(function () use ($website) {
            $website->update([
                'status' => WebsiteStatus::Published->value,
                'published_at' => $website->published_at ?? now(),
            ]);

            $website->pages()->whereNull('deleted_at')->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        });

        ActivityLog::record('published', "Website \"{$website->name}\" was published", $website, severity: 'success');

        return $website->fresh();
    }

    public function unpublish(Website $website): Website
    {
        $website->update(['status' => WebsiteStatus::Draft->value]);

        ActivityLog::record('unpublished', "Website \"{$website->name}\" was unpublished", $website);

        return $website->fresh();
    }

    public function delete(Website $website): void
    {
        $owner = $website->user;
        $website->delete();

        if ($owner && $owner->websites_count > 0) {
            $owner->decrement('websites_count');
        }
    }

    /* ------------------------------------------------------------------ */

    public function createHomepage(Website $website): Page
    {
        $page = Page::create([
            'website_id' => $website->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'sort_order' => 0,
            'status' => 'draft',
            'html' => $this->starterHtml($website),
            'css' => '',
            'seo' => ['title' => $website->name, 'description' => $website->description],
        ]);

        $website->update(['pages_count' => $website->pages()->count()]);

        return $page;
    }

    public function uniqueSubdomain(string $base): string
    {
        $slug = Str::slug(Str::limit($base, 40, ''));
        $slug = $slug !== '' ? $slug : 'site';
        $candidate = $slug;
        $i = 2;

        while (Website::withTrashed()->where('subdomain', $candidate)->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }

    private function defaultSettings(): array
    {
        return [
            'language' => 'en',
            'show_branding' => true,
            'cookie_banner' => false,
            'analytics_enabled' => true,
            'social' => ['twitter' => '', 'linkedin' => '', 'instagram' => ''],
        ];
    }

    private function starterHtml(Website $website): string
    {
        $name = e($website->name);

        return <<<HTML
        <section class="ab-hero" data-gjs-name="Hero">
          <div class="ab-container ab-hero__inner">
            <div class="ab-hero__copy">
              <span class="ab-pill">Welcome</span>
              <h1 class="ab-hero__title">{$name}</h1>
              <p class="ab-hero__sub">Start building by dragging blocks from the left panel, or let the AI generator compose a complete site for you.</p>
              <div class="ab-hero__cta">
                <a class="ab-btn ab-btn--primary ab-btn--lg" href="#">Get started</a>
              </div>
            </div>
          </div>
        </section>
        HTML;
    }
}
