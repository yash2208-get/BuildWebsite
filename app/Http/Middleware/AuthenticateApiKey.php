<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken() ?? $request->header('X-API-Key');

        if (blank($plain)) {
            return response()->json(['message' => 'API key required.'], 401);
        }

        $key = ApiKey::findByPlain($plain);

        if (! $key || $key->isExpired()) {
            return response()->json(['message' => 'Invalid or expired API key.'], 401);
        }

        $bucket = 'api-key:'.$key->id;

        if (RateLimiter::tooManyAttempts($bucket, $key->rate_limit)) {
            return response()->json([
                'message' => 'Rate limit exceeded.',
                'retry_after' => RateLimiter::availableIn($bucket),
            ], 429);
        }

        RateLimiter::hit($bucket, 60);

        $key->forceFill([
            'usage_count' => $key->usage_count + 1,
            'last_used_at' => now(),
            'last_used_ip' => $request->ip(),
        ])->saveQuietly();

        $request->attributes->set('api_key', $key);

        if ($key->user) {
            auth()->setUser($key->user);
        }

        return $next($request);
    }
}
