<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Enums\AiGenerationType;
use App\Models\AiGeneration;
use App\Models\Page;
use App\Models\Theme;
use App\Models\User;
use App\Models\Website;
use App\Services\Ai\AiManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns an AI generation into a real, editable website with pages and a theme.
 */
class AiWebsiteBuilder
{
    public function __construct(
        private readonly AiManager $ai,
        private readonly WebsiteService $websites,
    ) {}

    /**
     * Generate a complete multi-page website from a natural language brief.
     */
    public function buildWebsite(User $user, string $prompt, array $options = []): Website
    {
        $generation = $this->ai->generateFor($user, AiGenerationType::Website, $prompt, $options);
        $data = $generation->decodedResult();

        return DB::transaction(function () use ($user, $data, $options, $generation) {
            $brand = (string) ($data['brand'] ?? Str::title(Str::words($generation->prompt, 3, '')));

            $theme = $this->createTheme($user, $brand, $data);

            $website = $this->websites->create($user, [
                'name' => $brand,
                'description' => (string) data_get($data, 'pages.0.seo.description', ''),
                'category' => (string) ($options['industry'] ?? 'business'),
                'theme_id' => $theme->id,
                'skip_homepage' => true,
                'seo' => data_get($data, 'pages.0.seo', []),
            ]);

            $website->update([
                'custom_css' => (string) ($data['css'] ?? ''),
                'settings' => array_merge((array) $website->settings, [
                    'ai_generated' => true,
                    'ai_generation_id' => $generation->id,
                    'palette' => $data['palette'] ?? null,
                    'image_suggestions' => $data['image_suggestions'] ?? [],
                ]),
            ]);

            $this->createPages($website, (array) ($data['pages'] ?? []));

            $generation->update(['website_id' => $website->id]);

            return $website->fresh(['pages']);
        });
    }

    /**
     * Generate a single landing page inside an existing website.
     */
    public function buildLandingPage(User $user, Website $website, string $prompt, array $options = []): Page
    {
        $generation = $this->ai->generateFor(
            $user, AiGenerationType::LandingPage, $prompt, $options, $website->id
        );

        $data = $generation->decodedResult();
        $pageData = (array) data_get($data, 'pages.0', []);

        $page = Page::create([
            'website_id' => $website->id,
            'title' => (string) ($pageData['title'] ?? 'AI Landing Page'),
            'slug' => Str::slug((string) ($pageData['slug'] ?? 'landing-'.Str::lower(Str::random(5)))),
            'type' => 'landing',
            'html' => (string) ($pageData['html'] ?? ''),
            'css' => (string) ($pageData['css'] ?? $data['css'] ?? ''),
            'seo' => (array) ($pageData['seo'] ?? []),
            'sort_order' => (int) $website->pages()->max('sort_order') + 1,
            'status' => 'draft',
        ]);

        $website->update(['pages_count' => $website->pages()->count()]);

        return $page;
    }

    /** Generate a blog article and persist it. */
    public function buildBlogPost(User $user, ?Website $website, string $topic, array $options = []): AiGeneration
    {
        return $this->ai->generateFor(
            $user, AiGenerationType::Blog, $topic, $options, $website?->id
        );
    }

    /* ------------------------------------------------------------------ */

    private function createPages(Website $website, array $pages): void
    {
        foreach (array_values($pages) as $i => $p) {
            Page::create([
                'website_id' => $website->id,
                'title' => (string) ($p['title'] ?? 'Page '.($i + 1)),
                'slug' => Str::slug((string) ($p['slug'] ?? $p['title'] ?? 'page-'.($i + 1))),
                'html' => (string) ($p['html'] ?? ''),
                'css' => (string) ($p['css'] ?? ''),
                'seo' => (array) ($p['seo'] ?? []),
                'is_homepage' => (bool) ($p['is_homepage'] ?? $i === 0),
                'sort_order' => $i,
                'status' => 'draft',
            ]);
        }

        $website->update(['pages_count' => $website->pages()->count()]);
    }

    private function createTheme(User $user, string $brand, array $data): Theme
    {
        $palette = (array) ($data['palette'] ?? []);

        return Theme::create([
            'user_id' => $user->id,
            'name' => $brand.' Theme',
            'description' => 'Generated automatically from your AI brief.',
            'tokens' => [
                'colors' => [
                    'primary' => $palette['primary'] ?? '#6366f1',
                    'secondary' => $palette['secondary'] ?? '#8b5cf6',
                    'accent' => $palette['accent'] ?? '#06b6d4',
                    'background' => $palette['background'] ?? '#f8fafc',
                    'surface' => $palette['surface'] ?? '#ffffff',
                    'text' => $palette['text'] ?? '#0f172a',
                    'muted' => $palette['muted'] ?? '#64748b',
                ],
                'radius' => '18px',
            ],
            'typography' => (array) ($data['typography'] ?? ['heading' => 'Inter', 'body' => 'Inter']),
            'custom_css' => (string) ($data['css'] ?? ''),
            'is_global' => false,
        ]);
    }
}
