<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Platform\SettingsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            static $brand = null;

            if ($brand === null) {
                try {
                    $brand = app(SettingsService::class)->brand();
                } catch (\Throwable) {
                    $brand = [
                        'name' => config('platform.name'),
                        'tagline' => config('platform.tagline'),
                        'primary_color' => '#6366f1',
                        'accent_color' => '#22d3ee',
                        'support_email' => config('platform.support_email'),
                        'logo' => null,
                        'favicon' => null,
                    ];
                }
            }

            $view->with('brand', $brand);
        });
    }
}
