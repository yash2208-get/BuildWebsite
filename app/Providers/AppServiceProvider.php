<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\Ai\AiManager;
use App\Services\Platform\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(AiManager::class);
    }

    public function boot(): void
    {
        Model::preventLazyLoading($this->app->isLocal() && config('platform.strict_models', false));
        Model::unguard(false);

        Paginator::useTailwind();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(8));

        // Use the extended role/permission models so we get labels + colours.
        config([
            'permission.models.role' => Role::class,
            'permission.models.permission' => Permission::class,
        ]);

        $this->registerBladeDirectives();
    }

    private function registerBladeDirectives(): void
    {
        Blade::if('superadmin', fn () => auth()->check() && auth()->user()->isSuperAdmin());
        Blade::if('staff', fn () => auth()->check() && auth()->user()->isStaff());
        Blade::if('customer', fn () => auth()->check() && ! auth()->user()->isStaff());
    }
}
