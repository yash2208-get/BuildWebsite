<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Plan;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'plans' => Cache::remember('public.plans', 600, fn () => Plan::active()->get()),
            'templates' => Cache::remember('public.templates', 600, fn () => Template::active()->orderBy('sort_order')->limit(6)->get()),
            'faqs' => Cache::remember('public.faqs', 600, fn () => Faq::active()->limit(6)->get()),
        ]);
    }

    public function pricing(): View
    {
        return view('public.pricing', [
            'plans' => Plan::active()->get(),
            'faqs' => Faq::active()->where('category', 'billing')->get(),
        ]);
    }

    public function templates(Request $request): View
    {
        return view('public.templates', [
            'templates' => Template::active()
                ->category($request->string('category')->toString() ?: 'all')
                ->search($request->string('q')->toString())
                ->orderBy('sort_order')
                ->paginate(12)
                ->withQueryString(),
            'categories' => config('platform.template_categories'),
            'category' => $request->string('category')->toString() ?: 'all',
        ]);
    }

    public function features(): View
    {
        return view('public.features');
    }

    public function faq(): View
    {
        return view('public.faq', [
            'groups' => Faq::active()->get()->groupBy('category'),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($data + ['ip_address' => $request->ip()]);

        return back()->with('success', 'Thanks for reaching out — we will reply within one business day.');
    }

    public function page(string $slug): View
    {
        $page = CmsPage::published()->where('slug', $slug)->firstOrFail();

        return view('public.cms-page', ['page' => $page]);
    }
}
