<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Eloquent\WebsiteRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Repository bindings. Concrete implementations are swappable, which keeps
     * controllers and Livewire components dependent on abstractions only.
     */
    public array $bindings = [];

    public array $singletons = [
        WebsiteRepository::class => WebsiteRepository::class,
    ];
}
