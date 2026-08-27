<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free', 'slug' => 'free', 'tagline' => 'Everything you need to start',
                'description' => 'Perfect for trying the platform and shipping your first site.',
                'price_monthly' => 0, 'price_yearly' => 0, 'trial_days' => 0,
                'max_websites' => 1, 'max_pages' => 5, 'max_storage_mb' => 250, 'max_ai_credits' => 25,
                'max_team_members' => 1,
                'allows_custom_domain' => false, 'allows_export' => false,
                'allows_white_label' => false, 'allows_remove_branding' => false,
                'features' => ['1 website', '5 pages', 'Free subdomain', '25 AI credits / month', 'Community support'],
                'is_featured' => false, 'sort_order' => 1,
            ],
            [
                'name' => 'Starter', 'slug' => 'starter', 'tagline' => 'For freelancers and side projects',
                'description' => 'Connect your own domain and remove the platform badge.',
                'price_monthly' => 12, 'price_yearly' => 115, 'trial_days' => 14,
                'max_websites' => 3, 'max_pages' => 25, 'max_storage_mb' => 2048, 'max_ai_credits' => 150,
                'max_team_members' => 2,
                'allows_custom_domain' => true, 'allows_export' => false,
                'allows_white_label' => false, 'allows_remove_branding' => true,
                'features' => ['3 websites', '25 pages each', 'Custom domain', 'Remove branding', '150 AI credits / month', 'Email support'],
                'is_featured' => false, 'sort_order' => 2,
            ],
            [
                'name' => 'Professional', 'slug' => 'professional', 'tagline' => 'For growing teams',
                'description' => 'Unlimited pages, HTML export and priority support.',
                'price_monthly' => 29, 'price_yearly' => 278, 'trial_days' => 14,
                'max_websites' => 10, 'max_pages' => -1, 'max_storage_mb' => 10240, 'max_ai_credits' => 750,
                'max_team_members' => 5,
                'allows_custom_domain' => true, 'allows_export' => true,
                'allows_white_label' => false, 'allows_remove_branding' => true,
                'features' => ['10 websites', 'Unlimited pages', 'HTML export', 'Advanced analytics', '750 AI credits / month', 'Priority support'],
                'is_featured' => true, 'sort_order' => 3,
            ],
            [
                'name' => 'Business', 'slug' => 'business', 'tagline' => 'For agencies at scale',
                'description' => 'Unlimited everything, white-label and a dedicated manager.',
                'price_monthly' => 79, 'price_yearly' => 758, 'trial_days' => 14,
                'max_websites' => -1, 'max_pages' => -1, 'max_storage_mb' => 51200, 'max_ai_credits' => -1,
                'max_team_members' => 25,
                'allows_custom_domain' => true, 'allows_export' => true,
                'allows_white_label' => true, 'allows_remove_branding' => true,
                'features' => ['Unlimited websites', 'Unlimited AI credits', 'White-label', 'Team collaboration', 'API access', 'Dedicated manager'],
                'is_featured' => false, 'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
