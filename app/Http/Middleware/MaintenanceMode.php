<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Platform\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function __construct(private readonly SettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $enabled = $this->settings->isMaintenanceMode();
        } catch (\Throwable) {
            $enabled = false;
        }

        // Staff can always access the platform during maintenance.
        if ($enabled && ! $request->user()?->isStaff() && ! $request->routeIs('login', 'logout')) {
            return response()->view('errors.maintenance', [
                'message' => $this->settings->get('maintenance_message', 'We will be back shortly.'),
            ], 503);
        }

        return $next($request);
    }
}
