<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Http\Resources\WebsiteResource;
use App\Models\Website;
use App\Services\Website\WebsiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WebsiteApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $websites = Website::query()
            ->where('user_id', $request->user()->id)
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('updated_at')
            ->paginate(min(50, $request->integer('per_page', 15)));

        return WebsiteResource::collection($websites);
    }

    public function show(Request $request, Website $website): WebsiteResource
    {
        abort_unless($website->user_id === $request->user()->id, 404);

        return new WebsiteResource($website->load('pages', 'theme'));
    }

    public function pages(Request $request, Website $website): AnonymousResourceCollection
    {
        abort_unless($website->user_id === $request->user()->id, 404);

        return PageResource::collection($website->pages()->get());
    }

    public function publish(Request $request, Website $website, WebsiteService $service): WebsiteResource
    {
        abort_unless($website->user_id === $request->user()->id, 404);

        return new WebsiteResource($service->publish($website));
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->primaryRole()->value,
                'plan' => $user->activePlan()?->slug,
                'websites_count' => $user->websites()->count(),
                'ai_credits_remaining' => $user->aiCreditsRemaining(),
            ],
        ]);
    }
}
