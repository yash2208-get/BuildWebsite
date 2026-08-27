<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AiGenerationType;
use App\Models\Template;
use App\Services\Ai\Drivers\SimulatedDriver;
use App\Support\Ai\AiRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the starter template gallery by running the built-in AI engine across
 * a spread of industries — every template is a genuine multi-page site.
 */
class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $driver = new SimulatedDriver();

        $definitions = [
            ['Nimbus SaaS', 'saas', 'bold', 'A modern SaaS platform for product analytics', ['analytics', 'startup', 'dashboard']],
            ['Atelier Studio', 'agency', 'luxury', 'A boutique design studio for premium brands', ['agency', 'creative', 'portfolio']],
            ['Marketplace Co', 'ecommerce', 'friendly', 'An online store selling curated home goods', ['store', 'retail', 'shop']],
            ['Lens & Light', 'portfolio', 'professional', 'A photographer portfolio showcasing editorial work', ['photography', 'portfolio']],
            ['Fern & Flame', 'restaurant', 'luxury', 'A seasonal farm-to-table restaurant', ['food', 'hospitality']],
            ['Peak Fitness', 'fitness', 'bold', 'A strength and conditioning studio', ['gym', 'health', 'coaching']],
            ['Bright Academy', 'education', 'friendly', 'An online learning platform for creative skills', ['courses', 'learning']],
            ['Harbour Realty', 'realestate', 'professional', 'A premium real estate agency', ['property', 'listings']],
            ['Vault Finance', 'finance', 'technical', 'A fintech platform for business banking', ['fintech', 'banking']],
            ['Calm Clinic', 'health', 'friendly', 'A modern wellness and therapy practice', ['clinic', 'wellness']],
            ['Wander Travel', 'travel', 'playful', 'A boutique travel agency for slow travel', ['travel', 'tourism']],
            ['Open Hands', 'nonprofit', 'friendly', 'A non-profit supporting local communities', ['charity', 'community']],
        ];

        foreach ($definitions as $i => [$name, $industry, $tone, $prompt, $tags]) {
            $result = $driver->generate(new AiRequest(
                AiGenerationType::Website,
                $prompt,
                ['industry' => $industry, 'tone' => $tone, 'business_name' => $name],
            ));

            $data = $result->data;
            $pages = (array) ($data['pages'] ?? []);
            $home = $pages[0] ?? [];

            Template::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => ucfirst($prompt).'. Includes '.count($pages).' ready-to-edit pages.',
                    'category' => $industry === 'fitness' || $industry === 'health' ? 'health' : ($industry === 'finance' ? 'startup' : $industry),
                    'tags' => $tags,
                    'html' => $home['html'] ?? '',
                    'css' => $data['css'] ?? '',
                    'pages' => $pages,
                    'is_premium' => $i % 4 === 3,
                    'is_active' => true,
                    'rating' => round(4.3 + (($i % 7) / 10), 2),
                    'uses_count' => 40 + ($i * 23) % 500,
                    'sort_order' => $i,
                ],
            );
        }
    }
}
