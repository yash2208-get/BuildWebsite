<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Support\Str;

/**
 * Renders published pages into final HTML documents and produces exportable
 * static bundles.
 */
class PublishService
{
    /** Render a single page into a complete HTML document. */
    public function render(Page $page, bool $forExport = false): string
    {
        $website = $page->website;
        $seo = (array) $page->seo;
        $siteSeo = (array) $website?->seo;

        $title = $seo['title'] ?? $page->title.' — '.$website?->name;
        $description = $seo['description'] ?? ($siteSeo['description'] ?? '');
        $robots = $siteSeo['robots'] ?? 'index,follow';
        $lang = $website?->setting('language', 'en') ?? 'en';

        $themeCss = $website?->theme?->toCssVariables() ?? '';
        $css = trim(implode("\n", array_filter([
            $themeCss,
            $page->css,
            $website?->custom_css,
            $website?->theme?->custom_css,
        ])));

        $js = trim((string) ($page->js ?? '').' '.(string) ($website?->custom_js ?? ''));
        $head = (string) ($website?->head_scripts ?? '');
        $body = (string) ($website?->body_scripts ?? '');
        $favicon = $website?->favicon ? '<link rel="icon" href="'.e($website->favicon).'">' : '';

        $nav = $forExport ? $this->exportNav($page) : '';
        $branding = $this->branding($website, $forExport);
        $ogImage = $seo['og_image'] ?? $siteSeo['og_image'] ?? null;
        $ogTag = $ogImage ? '<meta property="og:image" content="'.e($ogImage).'">' : '';

        $fontLink = '<link rel="preconnect" href="https://fonts.googleapis.com">'
            .'<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            .'<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">';

        return <<<HTML
        <!DOCTYPE html>
        <html lang="{$lang}">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$this->esc($title)}</title>
        <meta name="description" content="{$this->esc($description)}">
        <meta name="robots" content="{$this->esc($robots)}">
        <meta property="og:title" content="{$this->esc($title)}">
        <meta property="og:description" content="{$this->esc($description)}">
        <meta property="og:type" content="website">
        {$ogTag}
        {$favicon}
        {$fontLink}
        <style>{$css}</style>
        {$head}
        </head>
        <body>
        {$nav}
        {$page->html}
        {$branding}
        {$body}
        <script>{$js}</script>
        </body>
        </html>
        HTML;
    }

    /**
     * Build a static export bundle: filename => file contents.
     *
     * @return array<string, string>
     */
    public function exportBundle(Website $website): array
    {
        $files = [];

        foreach ($website->pages()->get() as $page) {
            $name = $page->is_homepage ? 'index.html' : Str::slug($page->slug).'.html';
            $files[$name] = $this->render($page, forExport: true);
        }

        $files['robots.txt'] = "User-agent: *\nAllow: /\nSitemap: {$website->url}/sitemap.xml\n";
        $files['sitemap.xml'] = $this->sitemap($website);
        $files['README.txt'] = $this->readme($website);

        return $files;
    }

    public function sitemap(Website $website): string
    {
        $urls = '';

        foreach ($website->pages()->where('status', 'published')->get() as $page) {
            $loc = $this->esc($page->url);
            $mod = $page->updated_at?->toAtomString() ?? now()->toAtomString();
            $priority = $page->is_homepage ? '1.0' : '0.7';
            $urls .= "  <url><loc>{$loc}</loc><lastmod>{$mod}</lastmod><priority>{$priority}</priority></url>\n";
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n{$urls}</urlset>\n";
    }

    private function exportNav(Page $page): string
    {
        $links = $page->website?->pages()
            ->where('show_in_nav', true)
            ->orderBy('sort_order')
            ->get() ?? collect();

        if ($links->isEmpty()) {
            return '';
        }

        $items = $links->map(function (Page $p) use ($page) {
            $href = $p->is_homepage ? 'index.html' : Str::slug($p->slug).'.html';
            $active = $p->is($page) ? ' aria-current="page"' : '';

            return '<a href="'.$href.'"'.$active.'>'.$this->esc($p->title).'</a>';
        })->implode('');

        return '<div class="ab-export-nav" style="display:none">'.$items.'</div>';
    }

    private function branding(?Website $website, bool $forExport): string
    {
        if (! $website || $forExport) {
            return '';
        }

        $allowsRemoval = (bool) $website->user?->activePlan()?->allows_remove_branding;

        if ($allowsRemoval && ! $website->setting('show_branding', true)) {
            return '';
        }

        $platform = e((string) config('platform.name'));

        return <<<HTML
        <div style="position:fixed;right:16px;bottom:16px;z-index:9999;font:600 12px/1 Inter,sans-serif;">
          <a href="/" style="display:inline-flex;align-items:center;gap:7px;padding:9px 13px;border-radius:999px;background:rgba(15,23,42,.9);color:#fff;text-decoration:none;box-shadow:0 8px 24px -8px rgba(0,0,0,.5)">
            <span style="width:9px;height:9px;border-radius:3px;background:linear-gradient(135deg,#6366f1,#22d3ee)"></span>
            Built with {$platform}
          </a>
        </div>
        HTML;
    }

    private function readme(Website $website): string
    {
        return implode("\n", [
            $website->name,
            str_repeat('=', strlen($website->name)),
            '',
            'Static export generated by '.config('platform.name').' on '.now()->toDayDateTimeString().'.',
            '',
            'These files are completely self-contained — upload them to any static host',
            '(Netlify, Vercel, S3, GitHub Pages, nginx) and the site will work as-is.',
            '',
            'Pages: '.$website->pages()->count(),
        ]);
    }

    private function esc(?string $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}
