<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\WebsiteExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketing site
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/templates', [PublicController::class, 'templates'])->name('templates.public');
Route::get('/features', [PublicController::class, 'features'])->name('features');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->name('contact.submit')->middleware('throttle:6,1');
Route::get('/faq', [PublicController::class, 'faq'])->name('faq');
Route::get('/p/{slug}', [PublicController::class, 'page'])->name('page.show');

/*
|--------------------------------------------------------------------------
| Published websites (subdomain preview + form handling)
|--------------------------------------------------------------------------
*/
Route::get('/sites/{subdomain}', [SiteController::class, 'show'])->name('site.preview');
Route::get('/sites/{subdomain}/{slug}', [SiteController::class, 'page'])->name('site.page');
Route::post('/sites/{subdomain}/forms/{form}', [SiteController::class, 'submitForm'])
    ->name('site.form.submit')->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email')->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active.user'])->group(function () {

    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->homeRoute()))->name('dashboard');

    /* ---------------- User workspace ---------------- */
    Route::prefix('app')->name('app.')->group(function () {
        Route::get('/', App\Livewire\App\Dashboard::class)->name('dashboard');
        Route::get('/websites', App\Livewire\App\Websites::class)->name('websites');
        Route::get('/websites/{website}/settings', App\Livewire\App\WebsiteSettings::class)->name('website.settings');
        Route::get('/ai', App\Livewire\App\AiStudio::class)->name('ai');
        Route::get('/templates', App\Livewire\App\Templates::class)->name('templates');
        Route::get('/components', App\Livewire\App\Components::class)->name('components');
        Route::get('/themes', App\Livewire\App\Themes::class)->name('themes');
        Route::get('/media', App\Livewire\App\MediaLibrary::class)->name('media');
        Route::get('/analytics', App\Livewire\App\Analytics::class)->name('analytics');
        Route::get('/billing', App\Livewire\App\Billing::class)->name('billing');
        Route::get('/settings', App\Livewire\App\Settings::class)->name('settings');
    });

    /* ---------------- Builder ---------------- */
    Route::prefix('builder')->name('builder.')->group(function () {
        Route::get('/{website}', App\Livewire\Builder\Editor::class)->name('edit');
        Route::get('/{website}/page/{page}', App\Livewire\Builder\Editor::class)->name('page');
    });

    Route::get('/websites/{website}/export', WebsiteExportController::class)->name('website.export');

    /* ---------------- Support (all roles) ---------------- */
    Route::get('/support', App\Livewire\App\Support::class)->name('support.index');

    /* ---------------- Admin ---------------- */
    Route::prefix('admin')->name('admin.')->middleware('can:access-admin-panel')->group(function () {
        Route::get('/', App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/users', App\Livewire\Admin\Users::class)->name('users');
        Route::get('/websites', App\Livewire\Admin\Websites::class)->name('websites');
        Route::get('/templates', App\Livewire\Admin\Templates::class)->name('templates');
        Route::get('/components', App\Livewire\Admin\Components::class)->name('components');
        Route::get('/media', App\Livewire\Admin\Media::class)->name('media');
        Route::get('/blog', App\Livewire\Admin\Blog::class)->name('blog');
        Route::get('/tickets', App\Livewire\Admin\Tickets::class)->name('tickets');
        Route::get('/analytics', App\Livewire\Admin\Analytics::class)->name('analytics');
        Route::get('/reports', App\Livewire\Admin\Reports::class)->name('reports');
        Route::get('/activity', App\Livewire\Admin\ActivityLogs::class)->name('activity');
    });

    /* ---------------- Super Admin ---------------- */
    Route::prefix('super')->name('super.')->middleware('can:access-super-panel')->group(function () {
        Route::get('/', App\Livewire\Super\Dashboard::class)->name('dashboard');
        Route::get('/users', App\Livewire\Super\Users::class)->name('users');
        Route::get('/admins', App\Livewire\Super\Admins::class)->name('admins');
        Route::get('/roles', App\Livewire\Super\Roles::class)->name('roles');
        Route::get('/websites', App\Livewire\Super\Websites::class)->name('websites');
        Route::get('/templates', App\Livewire\Super\Templates::class)->name('templates');
        Route::get('/components', App\Livewire\Super\Components::class)->name('components');
        Route::get('/themes', App\Livewire\Super\Themes::class)->name('themes');
        Route::get('/media', App\Livewire\Super\Media::class)->name('media');
        Route::get('/plans', App\Livewire\Super\Plans::class)->name('plans');
        Route::get('/subscriptions', App\Livewire\Super\Subscriptions::class)->name('subscriptions');
        Route::get('/coupons', App\Livewire\Super\Coupons::class)->name('coupons');
        Route::get('/invoices', App\Livewire\Super\Invoices::class)->name('invoices');
        Route::get('/revenue', App\Livewire\Super\Revenue::class)->name('revenue');
        Route::get('/reports', App\Livewire\Super\Reports::class)->name('reports');
        Route::get('/blog', App\Livewire\Super\Blog::class)->name('blog');
        Route::get('/cms', App\Livewire\Super\CmsPages::class)->name('cms');
        Route::get('/faq', App\Livewire\Super\Faqs::class)->name('faq');
        Route::get('/tickets', App\Livewire\Super\Tickets::class)->name('tickets');
        Route::get('/contacts', App\Livewire\Super\Contacts::class)->name('contacts');
        Route::get('/ai', App\Livewire\Super\AiSettings::class)->name('ai');
        Route::get('/ai-logs', App\Livewire\Super\AiLogs::class)->name('ai-logs');
        Route::get('/api-keys', App\Livewire\Super\ApiKeys::class)->name('api-keys');
        Route::get('/settings', App\Livewire\Super\Settings::class)->name('settings');
        Route::get('/health', App\Livewire\Super\SystemHealth::class)->name('health');
        Route::get('/queue', App\Livewire\Super\QueueMonitor::class)->name('queue');
        Route::get('/database', App\Livewire\Super\DatabaseManager::class)->name('database');
        Route::get('/backups', App\Livewire\Super\Backups::class)->name('backups');
        Route::get('/activity', App\Livewire\Super\ActivityLogs::class)->name('activity');
        Route::get('/security', App\Livewire\Super\SecurityLogs::class)->name('security');
    });
});
