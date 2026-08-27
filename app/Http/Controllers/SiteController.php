<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\Website;
use App\Models\WebsiteAnalytic;
use App\Services\Website\PublishService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Serves published websites (and owner previews) as fully rendered documents.
 */
class SiteController extends Controller
{
    public function __construct(private readonly PublishService $publisher) {}

    public function show(string $subdomain): Response
    {
        $website = $this->resolve($subdomain);
        $page = $website->pages()->where('is_homepage', true)->first()
            ?? $website->pages()->orderBy('sort_order')->firstOrFail();

        return $this->renderPage($website, $page);
    }

    public function page(string $subdomain, string $slug): Response
    {
        $website = $this->resolve($subdomain);
        $page = $website->pages()->where('slug', $slug)->firstOrFail();

        return $this->renderPage($website, $page);
    }

    public function submitForm(Request $request, string $subdomain, Form $form): RedirectResponse
    {
        $website = $this->resolve($subdomain);

        abort_unless($form->website_id === $website->id && $form->is_active, 404);

        $validated = $request->validate($form->validationRules());

        if ($form->store_submissions) {
            FormSubmission::create([
                'form_id' => $form->id,
                'data' => $validated['data'] ?? $request->except(['_token']),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'referrer' => substr((string) $request->headers->get('referer'), 0, 255),
            ]);

            $form->increment('submissions_count');
        }

        return $form->redirect_url
            ? redirect()->away($form->redirect_url)
            : back()->with('success', $form->success_message ?: 'Thank you! Your message has been received.');
    }

    /* ------------------------------------------------------------------ */

    private function resolve(string $subdomain): Website
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $isOwner = auth()->check()
            && (auth()->id() === $website->user_id || auth()->user()->isStaff());

        abort_if(! $website->isPublished() && ! $isOwner, 404);
        abort_if($website->maintenance_mode && ! $isOwner, 503, 'This site is temporarily unavailable.');

        return $website;
    }

    private function renderPage(Website $website, Page $page): Response
    {
        $this->trackView($website, $page);

        return response($this->publisher->render($page))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    private function trackView(Website $website, Page $page): void
    {
        // Owners previewing their own drafts should not inflate analytics.
        if (auth()->check() && auth()->id() === $website->user_id) {
            return;
        }

        $website->increment('views_count');
        $page->increment('views_count');

        WebsiteAnalytic::query()->updateOrCreate(
            ['website_id' => $website->id, 'page_id' => null, 'date' => now()->toDateString()],
            [],
        );

        WebsiteAnalytic::query()
            ->where('website_id', $website->id)
            ->whereNull('page_id')
            ->whereDate('date', now()->toDateString())
            ->increment('views');
    }
}
