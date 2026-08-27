<?php

declare(strict_types=1);

namespace App\Services\Ai;

/**
 * Deterministic copy engine used by the built-in "Aurora" AI driver.
 *
 * It composes industry-aware, tone-aware marketing copy from structured
 * vocabularies. Because it is seeded from the prompt, the same brief always
 * produces the same site, which makes demos and tests reproducible while
 * still feeling authored rather than lorem-ipsum.
 */
final class Copywriter
{
    private int $cursor = 0;

    public function __construct(private readonly string $seed)
    {
        $this->cursor = (int) (crc32($seed) % 100000);
    }

    /* --------------------------------------------------------------- */

    private function pick(array $options): mixed
    {
        if ($options === []) {
            return '';
        }

        $value = $options[$this->cursor % count($options)];
        $this->cursor = (int) (($this->cursor * 31 + 17) % 1000003);

        return $value;
    }

    private function some(array $options, int $count): array
    {
        $out = [];
        $pool = array_values($options);

        for ($i = 0; $i < $count && $pool !== []; $i++) {
            $idx = $this->cursor % count($pool);
            $out[] = $pool[$idx];
            array_splice($pool, $idx, 1);
            $this->cursor = (int) (($this->cursor * 37 + 11) % 1000003);
        }

        return $out;
    }

    /* --------------------------------------------------------------- */

    public function headline(string $brand, string $industry, string $tone): string
    {
        $patterns = match ($tone) {
            'bold' => [
                'Ship faster. Grow harder.',
                "Stop guessing. Start scaling.",
                'The only :thing you will ever need',
                'Built for teams who refuse to wait',
            ],
            'luxury' => [
                'Crafted for those who expect more',
                'Where precision meets presence',
                'An uncompromising :thing experience',
                'Refined :thing, delivered beautifully',
            ],
            'playful' => [
                'Say hello to your new favourite :thing',
                'Work less. Wow more.',
                'The :thing that actually sparks joy',
                'Big results, zero headaches',
            ],
            'technical' => [
                'Production-grade :thing infrastructure',
                'Composable :thing built on open standards',
                'Deploy :thing at any scale',
                'Reliable :thing with 99.99% uptime',
            ],
            'friendly' => [
                'Everything you need to grow, in one place',
                'Making :thing simple for everyone',
                'Your :thing, finally handled',
                "Let's build something people love",
            ],
            default => [
                'The modern way to run your :thing',
                'Grow your business with confidence',
                ':brand helps teams do their best work',
                'Smarter :thing for ambitious teams',
            ],
        };

        $thing = $this->industryNoun($industry);

        return str_replace([':thing', ':brand'], [$thing, $brand], (string) $this->pick($patterns));
    }

    public function subheadline(string $brand, string $industry, string $tone): string
    {
        $benefit = $this->pick([
            'launch in minutes, not months',
            'save hours every single week',
            'delight customers at every step',
            'turn visitors into loyal fans',
            'scale without adding headcount',
        ]);

        $audience = $this->audience($industry);

        return ucfirst("{$brand} gives {$audience} everything needed to {$benefit}. No code, no chaos — just results you can measure.");
    }

    public function industryNoun(string $industry): string
    {
        return match ($industry) {
            'agency' => 'creative studio',
            'ecommerce' => 'online store',
            'portfolio' => 'portfolio',
            'restaurant' => 'restaurant',
            'fitness' => 'training programme',
            'education' => 'learning platform',
            'realestate' => 'property business',
            'finance' => 'finance workflow',
            'health' => 'practice',
            'travel' => 'travel brand',
            'nonprofit' => 'mission',
            default => 'software business',
        };
    }

    public function audience(string $industry): string
    {
        return match ($industry) {
            'agency' => 'studios and freelancers',
            'ecommerce' => 'merchants',
            'portfolio' => 'creators',
            'restaurant' => 'hospitality teams',
            'fitness' => 'coaches and studios',
            'education' => 'educators',
            'realestate' => 'agents and brokers',
            'finance' => 'finance teams',
            'health' => 'clinics and practitioners',
            'travel' => 'travel operators',
            'nonprofit' => 'changemakers',
            default => 'modern teams',
        };
    }

    /** @return array<int, array{title:string, body:string, icon:string}> */
    public function features(string $industry, int $count = 6): array
    {
        $library = [
            ['title' => 'Lightning performance', 'body' => 'Every page ships as clean, static-first markup with optimised assets and a perfect Core Web Vitals profile.', 'icon' => 'bolt'],
            ['title' => 'Designed to convert', 'body' => 'Conversion-tested sections, clear hierarchy and calls to action placed exactly where attention lands.', 'icon' => 'target'],
            ['title' => 'Fully responsive', 'body' => 'Pixel-perfect layouts from 320px phones to ultra-wide displays, with per-breakpoint control.', 'icon' => 'devices'],
            ['title' => 'SEO built in', 'body' => 'Structured data, sitemaps, canonical tags and social cards generated automatically for every page.', 'icon' => 'search'],
            ['title' => 'Secure by default', 'body' => 'Managed TLS, hardened headers, granular roles and full audit trails on every change.', 'icon' => 'shield'],
            ['title' => 'Analytics that matter', 'body' => 'Track visitors, sources and conversions in a privacy-first dashboard — no extra scripts required.', 'icon' => 'chart'],
            ['title' => 'Team collaboration', 'body' => 'Invite teammates, assign roles and review revisions with a complete version history.', 'icon' => 'users'],
            ['title' => 'Custom domains', 'body' => 'Connect any domain in a few clicks with automatic certificate provisioning and renewals.', 'icon' => 'globe'],
            ['title' => 'Content management', 'body' => 'A friendly editor for blogs, galleries and landing pages that non-technical teammates actually enjoy.', 'icon' => 'pencil'],
        ];

        return $this->some($library, $count);
    }

    /** @return array<int, array{quote:string, name:string, role:string, company:string}> */
    public function testimonials(string $brand, int $count = 3): array
    {
        $library = [
            ['quote' => "We replaced three tools with {$brand} and shipped our new site in a single afternoon.", 'name' => 'Amara Osei', 'role' => 'Head of Marketing', 'company' => 'Northwind'],
            ['quote' => "The builder feels like a design tool but outputs production-ready code. That combination is rare.", 'name' => 'Julian Reyes', 'role' => 'Founder', 'company' => 'Studio Kite'],
            ['quote' => "Our conversion rate climbed 34% within a month of relaunching on {$brand}.", 'name' => 'Priya Nair', 'role' => 'Growth Lead', 'company' => 'Lumen Labs'],
            ['quote' => "Onboarding took minutes. Our whole team was editing pages by day two.", 'name' => 'Tom Bekker', 'role' => 'Operations Director', 'company' => 'Fieldhouse'],
            ['quote' => "Support is genuinely excellent, and the platform simply does not go down.", 'name' => 'Sofia Marchetti', 'role' => 'CTO', 'company' => 'Arcadia'],
        ];

        return $this->some($library, $count);
    }

    /** @return array<int, array{question:string, answer:string}> */
    public function faqs(string $brand, int $count = 5): array
    {
        $library = [
            ['question' => 'Do I need to know how to code?', 'answer' => "Not at all. {$brand} is a visual builder — drag sections onto the canvas and edit text inline. Developers can still drop into custom CSS and JavaScript whenever they want."],
            ['question' => 'Can I use my own domain?', 'answer' => 'Yes. Connect any domain you own from the dashboard and we handle DNS verification and TLS certificates automatically.'],
            ['question' => 'What happens when I hit my plan limits?', 'answer' => 'Nothing breaks. We notify you before you reach a limit and you can upgrade at any time — changes apply instantly and are pro-rated.'],
            ['question' => 'Can I export my website?', 'answer' => 'On eligible plans you can export clean, dependency-free HTML and CSS at any time. Your content is always yours.'],
            ['question' => 'Is there a free trial?', 'answer' => 'Every paid plan includes a no-risk trial. No card is required to start building, and you can cancel with one click.'],
            ['question' => 'How does the AI generator work?', 'answer' => 'Describe your business in a sentence or two and the generator composes a full multi-section site — copy, layout, palette and imagery direction included. Everything remains fully editable.'],
        ];

        return $this->some($library, $count);
    }

    /** @return array<int, array{name:string, role:string, bio:string}> */
    public function team(int $count = 4): array
    {
        $library = [
            ['name' => 'Elena Whitfield', 'role' => 'Chief Executive', 'bio' => 'Fifteen years scaling product teams across three continents.'],
            ['name' => 'Marcus Chen', 'role' => 'Head of Design', 'bio' => 'Obsessed with typography, motion and the details nobody notices.'],
            ['name' => 'Aisha Rahman', 'role' => 'Engineering Lead', 'bio' => 'Builds resilient systems and mentors the next generation.'],
            ['name' => 'David Okonjo', 'role' => 'Customer Success', 'bio' => 'Believes support is a product feature, not a cost centre.'],
            ['name' => 'Freya Lindqvist', 'role' => 'Head of Growth', 'bio' => 'Turns data into decisions and decisions into revenue.'],
            ['name' => 'Rahul Mehta', 'role' => 'Product Manager', 'bio' => 'Translates messy problems into elegant roadmaps.'],
        ];

        return $this->some($library, $count);
    }

    /** @return array<int, array{name:string, price:string, period:string, features:array, featured:bool}> */
    public function pricing(): array
    {
        return [
            ['name' => 'Starter', 'price' => '0', 'period' => 'forever', 'featured' => false, 'features' => ['1 website', '10 pages', 'Free subdomain', 'Community support']],
            ['name' => 'Professional', 'price' => '29', 'period' => 'per month', 'featured' => true, 'features' => ['10 websites', 'Unlimited pages', 'Custom domain', 'Remove branding', 'Priority support']],
            ['name' => 'Business', 'price' => '79', 'period' => 'per month', 'featured' => false, 'features' => ['Unlimited websites', 'Team collaboration', 'HTML export', 'Advanced analytics', 'Dedicated manager']],
        ];
    }

    /** @return array<int, array{label:string, value:string}> */
    public function stats(): array
    {
        return $this->some([
            ['label' => 'Websites launched', 'value' => '48K+'],
            ['label' => 'Average uptime', 'value' => '99.99%'],
            ['label' => 'Countries served', 'value' => '120'],
            ['label' => 'Customer rating', 'value' => '4.9/5'],
            ['label' => 'Pages published', 'value' => '1.2M'],
            ['label' => 'Faster to launch', 'value' => '12×'],
        ], 4);
    }

    public function ctaHeading(string $brand): string
    {
        return (string) $this->pick([
            'Ready to build something remarkable?',
            "Start your next project with {$brand}",
            'Your best website is minutes away',
            'Join thousands of teams already shipping',
        ]);
    }

    public function about(string $brand, string $industry): string
    {
        $audience = $this->audience($industry);

        return "{$brand} exists to give {$audience} a genuinely better way to work. "
            .'We obsess over the details — performance, accessibility, and craft — so the people using our '
            .'product can focus on what they do best. Every decision starts with a simple question: does this '
            .'make our customers measurably more successful?';
    }

    /** @return string[] */
    public function navigation(): array
    {
        return ['Home', 'Features', 'Pricing', 'About', 'Contact'];
    }

    public function imageKeywords(string $industry): array
    {
        return match ($industry) {
            'agency' => ['creative studio workspace', 'design team collaborating', 'moodboard flat lay', 'modern office interior'],
            'ecommerce' => ['product photography studio', 'packaging flat lay', 'boutique retail interior', 'delivery unboxing'],
            'restaurant' => ['plated fine dining dish', 'warm restaurant interior', 'chef at work', 'coffee and pastries'],
            'fitness' => ['gym training session', 'yoga studio light', 'running outdoors sunrise', 'healthy meal prep'],
            'education' => ['students collaborating', 'modern classroom', 'online learning laptop', 'library study space'],
            'realestate' => ['modern home exterior', 'bright living room', 'city skyline apartment', 'keys handover'],
            'health' => ['calm clinic interior', 'medical consultation', 'wellness treatment room', 'smiling practitioner'],
            'travel' => ['coastal landscape golden hour', 'boutique hotel room', 'city street travel', 'mountain hiking trail'],
            'nonprofit' => ['community volunteers', 'hands together outdoors', 'workshop group', 'sustainable project'],
            default => ['modern dashboard on laptop', 'team working in bright office', 'abstract gradient technology', 'developer at desk'],
        };
    }
}
