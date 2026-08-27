<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI driver
    |--------------------------------------------------------------------------
    | "simulated" ships with the platform and produces high quality, deterministic
    | layouts without an external API — perfect for demos, local development and
    | offline environments. Swap to "openai" (or any driver you register) in
    | production by setting AI_DRIVER and the matching credentials.
    */
    'driver' => env('AI_DRIVER', 'simulated'),

    'drivers' => [
        'simulated' => [
            'label' => 'Aurora Engine (built-in)',
            'model' => 'aurora-compose-1',
        ],

        'openai' => [
            'label' => 'OpenAI',
            'api_key' => env('OPENAI_API_KEY'),
            'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 60),
        ],

        'anthropic' => [
            'label' => 'Anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_uri' => env('ANTHROPIC_BASE_URI', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
            'timeout' => (int) env('ANTHROPIC_TIMEOUT', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Generation guard rails
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'prompt_max_chars' => 2000,
        'per_minute' => 10,
        'max_sections' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Industry presets used by the built-in engine to compose on-brand copy
    |--------------------------------------------------------------------------
    */
    'industries' => [
        'saas' => 'SaaS & Software',
        'agency' => 'Agency & Studio',
        'ecommerce' => 'E-commerce',
        'portfolio' => 'Portfolio',
        'restaurant' => 'Restaurant & Food',
        'fitness' => 'Fitness & Wellness',
        'education' => 'Education',
        'realestate' => 'Real Estate',
        'finance' => 'Finance',
        'health' => 'Healthcare',
        'travel' => 'Travel',
        'nonprofit' => 'Non-profit',
    ],

    'tones' => [
        'professional' => 'Professional',
        'friendly' => 'Friendly',
        'bold' => 'Bold & Punchy',
        'luxury' => 'Luxury',
        'playful' => 'Playful',
        'technical' => 'Technical',
    ],
];
