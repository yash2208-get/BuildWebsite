<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Theme;
use App\Services\Ai\PaletteFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['Aurora', 'indigo', 'Inter', 'Modern, confident and versatile — our default look.'],
            ['Evergreen', 'emerald', 'Inter', 'Calm, natural tones for wellness and sustainability brands.'],
            ['Sunset', 'sunset', 'Poppins', 'Warm and energetic, made for food and lifestyle.'],
            ['Deep Ocean', 'ocean', 'IBM Plex Sans', 'Trustworthy blues for finance and technology.'],
            ['Rose Quartz', 'rose', 'Playfair Display', 'Elegant and editorial, ideal for beauty and fashion.'],
            ['Ultraviolet', 'violet', 'Archivo', 'Bold and futuristic for creative studios.'],
            ['Monochrome', 'slate', 'Inter', 'Minimal greyscale that lets your content lead.'],
            ['Golden Hour', 'amber', 'Poppins', 'Sunlit warmth for hospitality and travel.'],
            ['Lagoon', 'teal', 'Inter', 'Fresh and clean for healthcare and SaaS.'],
        ];

        foreach ($themes as $i => [$name, $scheme, $font, $description]) {
            $palette = (new PaletteFactory($name))->make($scheme);

            Theme::updateOrCreate(
                ['slug' => Str::slug($name), 'user_id' => null],
                [
                    'name' => $name,
                    'description' => $description,
                    'tokens' => [
                        'colors' => [
                            'primary' => $palette['primary'],
                            'secondary' => $palette['secondary'],
                            'accent' => $palette['accent'],
                            'background' => $palette['background'],
                            'surface' => $palette['surface'],
                            'text' => $palette['text'],
                            'muted' => $palette['muted'],
                            'border' => $palette['border'],
                        ],
                        'radius' => '18px',
                        'swatches' => $palette['swatches'],
                    ],
                    'typography' => ['heading' => $font, 'body' => 'Inter', 'scale' => 1.25],
                    'spacing' => ['section' => 96, 'gutter' => 24],
                    'is_global' => true,
                    'is_active' => true,
                ],
            );
        }
    }
}
