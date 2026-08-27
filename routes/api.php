<?php

declare(strict_types=1);

use App\Http\Controllers\Api\WebsiteApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API (authenticated with platform API keys)
|--------------------------------------------------------------------------
*/
Route::middleware(['api.key'])->prefix('v1')->group(function () {
    Route::get('/websites', [WebsiteApiController::class, 'index']);
    Route::get('/websites/{website}', [WebsiteApiController::class, 'show']);
    Route::get('/websites/{website}/pages', [WebsiteApiController::class, 'pages']);
    Route::post('/websites/{website}/publish', [WebsiteApiController::class, 'publish']);
    Route::get('/me', [WebsiteApiController::class, 'me']);
});
