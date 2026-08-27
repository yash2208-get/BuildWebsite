<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['Privacy Policy', 'privacy', 'We collect the minimum data required to operate the service. This page explains what we store, why we store it, and how you can request deletion at any time.'],
            ['Terms of Service', 'terms', 'By using this platform you agree to these terms. We provide the service as-is, maintain reasonable uptime, and you retain full ownership of the content you create.'],
            ['Cookie Policy', 'cookies', 'We use essential cookies to keep you signed in and optional analytics cookies to understand how the product is used.'],
            ['About Us', 'about', 'We are a small, focused team building the website tool we always wanted: fast, beautiful, and genuinely easy to use.'],
        ];

        foreach ($pages as $i => [$title, $slug, $body]) {
            CmsPage::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'content' => "<h2>{$title}</h2><p>{$body}</p><p>Last updated ".now()->format('F Y').'.</p>',
                'status' => 'published',
                'show_in_footer' => true,
                'sort_order' => $i,
                'seo' => ['title' => $title, 'description' => mb_substr($body, 0, 155)],
            ]);
        }

        $faqs = [
            ['general', 'What is this platform?', 'A complete AI-powered website builder. Describe your business and get a full multi-page site you can refine in a visual drag-and-drop editor.'],
            ['general', 'Do I need coding skills?', 'No. Everything is visual. Developers can still add custom CSS and JavaScript when they want finer control.'],
            ['billing', 'Can I change plans later?', 'Yes — upgrade or downgrade at any time. Changes are pro-rated automatically.'],
            ['billing', 'Do you offer refunds?', 'If the platform is not right for you, contact support within 14 days of payment for a full refund.'],
            ['domains', 'Can I use my own domain?', 'Absolutely. Add your domain in the website settings and follow the DNS instructions we generate for you.'],
            ['domains', 'Is HTTPS included?', 'Yes. Certificates are provisioned and renewed automatically for every domain.'],
            ['ai', 'How many AI credits do I get?', 'Free accounts include 25 credits per month. Paid plans include significantly more, and the Business plan is unlimited.'],
            ['ai', 'Can I edit AI generated content?', 'Everything the AI produces is fully editable — it is normal content and markup, not a locked black box.'],
            ['technical', 'Can I export my website?', 'On eligible plans you can export clean, dependency-free HTML and CSS at any time.'],
            ['technical', 'Where are sites hosted?', 'On our global edge network with automatic caching, or export and host anywhere you like.'],
        ];

        foreach ($faqs as $i => [$cat, $q, $a]) {
            Faq::updateOrCreate(['question' => $q], [
                'answer' => $a,
                'category' => $cat,
                'is_active' => true,
                'sort_order' => $i,
                'helpful_count' => 10 + ($i * 7) % 90,
            ]);
        }
    }
}
