<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Platform branding
    |--------------------------------------------------------------------------
    */
    'name' => env('PLATFORM_NAME', 'AuroraBuild'),
    'tagline' => env('PLATFORM_TAGLINE', 'The AI website builder for modern teams'),
    'support_email' => env('PLATFORM_SUPPORT_EMAIL', 'support@aurorabuild.app'),
    'subdomain_host' => env('PLATFORM_SUBDOMAIN_HOST', 'aurorabuild.app'),

    /*
    |--------------------------------------------------------------------------
    | Activity / security logging
    |--------------------------------------------------------------------------
    */
    'activity_log' => [
        'enabled' => env('ACTIVITY_LOG_ENABLED', true),
        'prune_days' => env('ACTIVITY_LOG_PRUNE_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Builder defaults
    |--------------------------------------------------------------------------
    */
    'builder' => [
        'autosave_seconds' => 20,
        'max_revisions' => 30,
        'breakpoints' => [
            'desktop' => ['label' => 'Desktop', 'width' => null, 'icon' => 'monitor'],
            'tablet' => ['label' => 'Tablet', 'width' => 820, 'icon' => 'tablet'],
            'mobile' => ['label' => 'Mobile', 'width' => 390, 'icon' => 'phone'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size_kb' => env('UPLOAD_MAX_SIZE_KB', 8192),
        'image_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'],
        'document_mimes' => ['pdf', 'doc', 'docx', 'txt', 'csv', 'xlsx'],
        'video_mimes' => ['mp4', 'webm', 'mov'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default quotas used when a user has no active subscription
    |--------------------------------------------------------------------------
    */
    'free_quota' => [
        'max_websites' => 3,
        'max_pages' => 10,
        'max_storage_mb' => 250,
        'max_ai_credits' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Component / template categories used across the builder libraries
    |--------------------------------------------------------------------------
    */
    'component_categories' => [
        'header' => 'Headers',
        'navbar' => 'Navbars',
        'hero' => 'Hero Sections',
        'features' => 'Features',
        'pricing' => 'Pricing',
        'faq' => 'FAQ',
        'testimonials' => 'Testimonials',
        'team' => 'Team',
        'gallery' => 'Gallery',
        'contact' => 'Contact',
        'blog' => 'Blog',
        'forms' => 'Forms',
        'cta' => 'Call To Action',
        'stats' => 'Stats',
        'logos' => 'Logo Clouds',
        'footer' => 'Footers',
        'content' => 'Content',
        'media' => 'Media',
        'ecommerce' => 'Commerce',
    ],

    'template_categories' => [
        'saas' => 'SaaS',
        'agency' => 'Agency',
        'portfolio' => 'Portfolio',
        'ecommerce' => 'E-commerce',
        'restaurant' => 'Restaurant',
        'startup' => 'Startup',
        'blog' => 'Blog',
        'event' => 'Event',
        'education' => 'Education',
        'health' => 'Health',
        'realestate' => 'Real Estate',
        'personal' => 'Personal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Typography
    |--------------------------------------------------------------------------
    | Fonts offered inside the theme builder and the visual editor.
    */
    'fonts' => [
        'Inter' => 'Inter',
        'Manrope' => 'Manrope',
        'Poppins' => 'Poppins',
        'DM Sans' => 'DM Sans',
        'Plus Jakarta Sans' => 'Plus Jakarta Sans',
        'Space Grotesk' => 'Space Grotesk',
        'Sora' => 'Sora',
        'Outfit' => 'Outfit',
        'Playfair Display' => 'Playfair Display',
        'Lora' => 'Lora',
        'Merriweather' => 'Merriweather',
        'JetBrains Mono' => 'JetBrains Mono',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom domains
    |--------------------------------------------------------------------------
    */
    'dns_a_record' => env('PLATFORM_DNS_A', '203.0.113.10'),

];
