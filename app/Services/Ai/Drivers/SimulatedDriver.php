<?php

declare(strict_types=1);

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriverContract;
use App\Enums\AiGenerationType;
use App\Services\Ai\Copywriter;
use App\Services\Ai\PaletteFactory;
use App\Services\Ai\SectionRenderer;
use App\Support\Ai\AiRequest;
use App\Support\Ai\AiResult;
use Illuminate\Support\Str;

/**
 * The built-in "Aurora" engine.
 *
 * Produces complete, production-quality websites without calling an external
 * API. Results are deterministic for a given prompt which keeps demos, tests
 * and offline development fully reproducible.
 */
final class SimulatedDriver implements AiDriverContract
{
    public function name(): string
    {
        return 'simulated';
    }

    public function model(): string
    {
        return (string) config('ai.drivers.simulated.model', 'aurora-compose-1');
    }

    public function generate(AiRequest $request): AiResult
    {
        $started = hrtime(true);

        $data = match ($request->type) {
            AiGenerationType::Website => $this->website($request),
            AiGenerationType::LandingPage => $this->landing($request),
            AiGenerationType::Content => $this->content($request),
            AiGenerationType::Blog => $this->blog($request),
            AiGenerationType::Palette => $this->palette($request),
            AiGenerationType::ImageSuggestion => $this->images($request),
            AiGenerationType::DesignSuggestion => $this->design($request),
        };

        $ms = (int) ((hrtime(true) - $started) / 1_000_000);

        return new AiResult(
            data: $data,
            provider: $this->name(),
            model: $this->model(),
            tokensUsed: (int) (Str::length(json_encode($data) ?: '') / 4),
            durationMs: $ms,
        );
    }

    /* ------------------------------------------------------------------ */

    private function website(AiRequest $request): array
    {
        $writer = new Copywriter($request->prompt.$request->industry());
        $brand = $request->businessName();
        $industry = $request->industry();
        $tone = $request->tone();

        $palette = (new PaletteFactory($request->prompt.$industry))->make($request->option('color_scheme'));
        $renderer = new SectionRenderer($palette);
        $nav = $writer->navigation();
        $hints = $writer->imageKeywords($industry);

        $home = implode("\n", [
            $renderer->navbar($brand, $nav),
            $renderer->hero(
                $writer->headline($brand, $industry, $tone),
                $writer->subheadline($brand, $industry, $tone),
                $hints[0] ?? 'hero background'
            ),
            $renderer->logos(),
            $renderer->features($writer->features($industry, 6)),
            $renderer->stats($writer->stats()),
            $renderer->about('Built with intent', $writer->about($brand, $industry), $hints[1] ?? 'team'),
            $renderer->testimonials($writer->testimonials($brand, 3)),
            $renderer->pricing($writer->pricing()),
            $renderer->faq($writer->faqs($brand, 5)),
            $renderer->cta($writer->ctaHeading($brand)),
            $renderer->footer($brand, $nav),
        ]);

        $about = implode("\n", [
            $renderer->navbar($brand, $nav),
            $renderer->about('Our story', $writer->about($brand, $industry), $hints[1] ?? 'team'),
            $renderer->team($writer->team(4)),
            $renderer->stats($writer->stats()),
            $renderer->cta($writer->ctaHeading($brand)),
            $renderer->footer($brand, $nav),
        ]);

        $pricing = implode("\n", [
            $renderer->navbar($brand, $nav),
            $renderer->pricing($writer->pricing()),
            $renderer->faq($writer->faqs($brand, 5)),
            $renderer->cta($writer->ctaHeading($brand)),
            $renderer->footer($brand, $nav),
        ]);

        $contact = implode("\n", [
            $renderer->navbar($brand, $nav),
            $renderer->contact($brand),
            $renderer->footer($brand, $nav),
        ]);

        $gallery = implode("\n", [
            $renderer->navbar($brand, $nav),
            $renderer->gallery($hints),
            $renderer->cta($writer->ctaHeading($brand)),
            $renderer->footer($brand, $nav),
        ]);

        $css = $renderer->stylesheet();

        return [
            'brand' => $brand,
            'industry' => $industry,
            'tone' => $tone,
            'palette' => $palette,
            'typography' => $this->typography($tone),
            'css' => $css,
            'image_suggestions' => $hints,
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'html' => $home, 'css' => $css,
                    'seo' => ['title' => "{$brand} — ".$writer->headline($brand, $industry, $tone), 'description' => $writer->subheadline($brand, $industry, $tone)]],
                ['title' => 'About', 'slug' => 'about', 'is_homepage' => false, 'html' => $about, 'css' => $css,
                    'seo' => ['title' => "About {$brand}", 'description' => "Learn about the team and mission behind {$brand}."]],
                ['title' => 'Pricing', 'slug' => 'pricing', 'is_homepage' => false, 'html' => $pricing, 'css' => $css,
                    'seo' => ['title' => "Pricing — {$brand}", 'description' => 'Simple, transparent pricing that scales with you.']],
                ['title' => 'Gallery', 'slug' => 'gallery', 'is_homepage' => false, 'html' => $gallery, 'css' => $css,
                    'seo' => ['title' => "Gallery — {$brand}", 'description' => 'A closer look at our work.']],
                ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'html' => $contact, 'css' => $css,
                    'seo' => ['title' => "Contact {$brand}", 'description' => "Get in touch with the {$brand} team."]],
            ],
        ];
    }

    private function landing(AiRequest $request): array
    {
        $writer = new Copywriter($request->prompt.'landing');
        $brand = $request->businessName();
        $industry = $request->industry();
        $tone = $request->tone();

        $palette = (new PaletteFactory($request->prompt.'landing'))->make($request->option('color_scheme'));
        $renderer = new SectionRenderer($palette);
        $nav = ['Features', 'Pricing', 'FAQ'];
        $hints = $writer->imageKeywords($industry);

        $html = implode("\n", [
            $renderer->navbar($brand, $nav),
            $renderer->hero(
                $writer->headline($brand, $industry, $tone),
                $writer->subheadline($brand, $industry, $tone),
                $hints[0] ?? 'hero'
            ),
            $renderer->logos(),
            $renderer->features($writer->features($industry, 3)),
            $renderer->testimonials($writer->testimonials($brand, 3)),
            $renderer->pricing($writer->pricing()),
            $renderer->faq($writer->faqs($brand, 4)),
            $renderer->cta($writer->ctaHeading($brand)),
            $renderer->footer($brand, $nav),
        ]);

        return [
            'brand' => $brand,
            'palette' => $palette,
            'css' => $renderer->stylesheet(),
            'image_suggestions' => $hints,
            'pages' => [[
                'title' => $brand.' — Landing',
                'slug' => 'landing-'.Str::lower(Str::random(5)),
                'is_homepage' => false,
                'html' => $html,
                'css' => $renderer->stylesheet(),
                'seo' => ['title' => $writer->headline($brand, $industry, $tone), 'description' => $writer->subheadline($brand, $industry, $tone)],
            ]],
        ];
    }

    private function content(AiRequest $request): array
    {
        $writer = new Copywriter($request->prompt);
        $brand = $request->businessName();
        $industry = $request->industry();
        $tone = $request->tone();
        $kind = (string) $request->option('content_type', 'paragraph');

        $variants = match ($kind) {
            'headline' => [
                $writer->headline($brand, $industry, $tone),
                $writer->headline($brand.' ', $industry, 'bold'),
                $writer->headline($brand.'  ', $industry, 'friendly'),
            ],
            'tagline' => [
                'Build faster. Launch sooner.',
                'Where great websites begin.',
                'Design without limits.',
            ],
            'cta' => [
                $writer->ctaHeading($brand),
                'Start building for free today',
                'See what you can ship this week',
            ],
            'bullets' => array_map(
                fn ($f) => $f['title'].' — '.$f['body'],
                $writer->features($industry, 5)
            ),
            'about' => [$writer->about($brand, $industry)],
            default => [
                $writer->subheadline($brand, $industry, $tone),
                $writer->about($brand, $industry),
            ],
        };

        return [
            'content_type' => $kind,
            'variants' => array_values($variants),
            'primary' => $variants[0] ?? '',
            'word_count' => str_word_count((string) ($variants[0] ?? '')),
        ];
    }

    private function blog(AiRequest $request): array
    {
        $writer = new Copywriter($request->prompt.'blog');
        $topic = trim($request->prompt) !== '' ? trim($request->prompt) : 'Building better websites';
        $title = Str::title(Str::limit($topic, 70, ''));
        $industry = $request->industry();
        $features = $writer->features($industry, 4);

        $intro = "Every team eventually asks the same question: how do we ship a website that looks "
            ."exceptional, loads instantly, and can be updated without a developer in the loop? "
            ."This guide walks through the practical answer, drawn from what consistently works.";

        $body = "<h2>Why this matters now</h2>\n<p>{$intro}</p>\n";
        $body .= "<p>Expectations have shifted. Visitors judge credibility within a few hundred milliseconds, "
            ."and search engines increasingly reward the same signals that make humans happy: speed, clarity "
            ."and genuine usefulness.</p>\n";

        $body .= "<h2>The essentials</h2>\n";

        foreach ($features as $i => $f) {
            $n = $i + 1;
            $body .= "<h3>{$n}. {$f['title']}</h3>\n<p>{$f['body']}</p>\n";
        }

        $body .= "<h2>Putting it into practice</h2>\n";
        $body .= "<p>Start with a single page and get it genuinely right — hierarchy, message, and a clear next "
            ."step. Then expand. Teams that iterate weekly consistently outperform those that spend six months "
            ."on a big-bang redesign.</p>\n";
        $body .= "<blockquote><p>Ship something small, measure honestly, and improve relentlessly.</p></blockquote>\n";
        $body .= "<h2>Conclusion</h2>\n<p>You do not need a large budget or a long timeline. You need a clear "
            ."message, a fast foundation, and the ability to make changes the moment you learn something new.</p>";

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => Str::limit(strip_tags($intro), 180),
            'content' => $body,
            'tags' => array_slice(['web design', 'performance', 'conversion', 'seo', 'strategy'], 0, 4),
            'seo' => [
                'title' => $title,
                'description' => Str::limit(strip_tags($intro), 155),
            ],
            'reading_minutes' => max(2, (int) ceil(str_word_count(strip_tags($body)) / 200)),
        ];
    }

    private function palette(AiRequest $request): array
    {
        $factory = new PaletteFactory($request->prompt);

        return [
            'palettes' => $factory->suggestions(5),
            'recommended' => $factory->make($request->option('color_scheme')),
        ];
    }

    private function images(AiRequest $request): array
    {
        $writer = new Copywriter($request->prompt);
        $keywords = $writer->imageKeywords($request->industry());

        $suggestions = [];

        foreach ($keywords as $k) {
            $suggestions[] = [
                'keyword' => $k,
                'prompt' => "Editorial photograph of {$k}, natural light, shallow depth of field, muted modern palette, high resolution",
                'placement' => match (true) {
                    str_contains($k, 'team') || str_contains($k, 'office') => 'About section',
                    str_contains($k, 'product') || str_contains($k, 'dashboard') => 'Hero / feature showcase',
                    default => 'Gallery or supporting section',
                },
                'aspect' => '16:9',
            ];
        }

        return ['suggestions' => $suggestions, 'count' => count($suggestions)];
    }

    private function design(AiRequest $request): array
    {
        $palette = (new PaletteFactory($request->prompt))->make($request->option('color_scheme'));
        $tone = $request->tone();

        return [
            'palette' => $palette,
            'typography' => $this->typography($tone),
            'recommendations' => [
                ['area' => 'Hierarchy', 'advice' => 'Lead with one dominant headline per screen. Anything competing for attention should be demoted to supporting text or removed entirely.'],
                ['area' => 'Spacing', 'advice' => 'Use a consistent 8px rhythm and give sections at least 96px of vertical breathing room on desktop, 64px on mobile.'],
                ['area' => 'Colour', 'advice' => 'Keep the primary colour for actions only. If everything is emphasised, nothing is — restraint is what reads as premium.'],
                ['area' => 'Motion', 'advice' => 'Animate at 150–250ms with an ease-out curve. Respect prefers-reduced-motion for accessibility.'],
                ['area' => 'Contrast', 'advice' => 'Body text should hit at least 4.5:1 against its background; large display text can drop to 3:1.'],
                ['area' => 'Imagery', 'advice' => 'Pick one photographic treatment and apply it everywhere. Mixed styles are the fastest way to look unfinished.'],
            ],
            'layout_grid' => ['columns' => 12, 'gutter' => 24, 'max_width' => 1160],
        ];
    }

    private function typography(string $tone): array
    {
        return match ($tone) {
            'luxury' => ['heading' => 'Playfair Display', 'body' => 'Inter', 'scale' => 1.28],
            'playful' => ['heading' => 'Poppins', 'body' => 'Inter', 'scale' => 1.22],
            'technical' => ['heading' => 'IBM Plex Sans', 'body' => 'IBM Plex Sans', 'scale' => 1.2],
            'bold' => ['heading' => 'Archivo', 'body' => 'Inter', 'scale' => 1.32],
            default => ['heading' => 'Inter', 'body' => 'Inter', 'scale' => 1.25],
        };
    }
}
