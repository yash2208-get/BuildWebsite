<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Media;
use App\Models\Page;
use App\Models\SupportTicket;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use App\Policies\MediaPolicy;
use App\Policies\PagePolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\TemplatePolicy;
use App\Policies\UserPolicy;
use App\Policies\WebsitePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Website::class => WebsitePolicy::class,
        Page::class => PagePolicy::class,
        Media::class => MediaPolicy::class,
        User::class => UserPolicy::class,
        Template::class => TemplatePolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super admins implicitly receive every ability.
        Gate::before(fn (User $user, string $ability) => $user->isSuperAdmin() ? true : null);

        Gate::define('access-admin-panel', fn (User $user) => $user->isStaff());
        Gate::define('access-super-panel', fn (User $user) => $user->isSuperAdmin());
        Gate::define('impersonate', fn (User $user) => $user->can('users.impersonate'));
    }
}
