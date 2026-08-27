<?php

declare(strict_types=1);

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriverContract;
use App\Support\Ai\AiRequest;
use App\Support\Ai\AiResult;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OpenAI-compatible chat-completions driver.
 *
 * Any provider exposing the /chat/completions contract (OpenAI, Azure OpenAI,
 * Groq, OpenRouter, local vLLM…) can be used by pointing OPENAI_BASE_URI at it.
 */
final class OpenAiDriver implements AiDriverContract
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUri,
        private readonly string $model,
        private readonly int $timeout = 60,
    ) {}

    public function name(): string
    {
        return 'openai';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function generate(AiRequest $request): AiResult
    {
        if (blank($this->apiKey)) {
            throw new RuntimeException('The OpenAI driver requires OPENAI_API_KEY to be configured.');
        }

        $started = hrtime(true);

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post(rtrim($this->baseUri, '/').'/chat/completions', [
                'model' => $this->model,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($request)],
                    ['role' => 'user', 'content' => $request->prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('AI provider error: '.$response->status().' '.$response->body());
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '{}');
        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new RuntimeException('The AI provider returned a malformed response.');
        }

        return new AiResult(
            data: $data,
            provider: $this->name(),
            model: $this->model,
            tokensUsed: (int) data_get($response->json(), 'usage.total_tokens', 0),
            durationMs: (int) ((hrtime(true) - $started) / 1_000_000),
        );
    }

    private function systemPrompt(AiRequest $request): string
    {
        return implode(' ', [
            'You are an expert web designer and conversion copywriter.',
            'Return strict JSON only, matching the schema for a "'.$request->type->value.'" generation.',
            'For website/landing generations return: {brand, palette:{primary,secondary,accent,background,surface,text,muted}, css, pages:[{title,slug,is_homepage,html,css,seo:{title,description}}]}.',
            'HTML must be semantic, self-contained, responsive and free of external dependencies.',
            'Industry: '.$request->industry().'. Tone: '.$request->tone().'.',
        ]);
    }
}
