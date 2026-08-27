<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiDriverContract;
use App\Enums\AiGenerationType;
use App\Models\AiGeneration;
use App\Models\User;
use App\Services\Ai\Drivers\OpenAiDriver;
use App\Services\Ai\Drivers\SimulatedDriver;
use App\Support\Ai\AiRequest;
use App\Support\Ai\AiResult;
use App\Support\Exceptions\QuotaExceededException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Resolves AI drivers, enforces credit quotas and records every generation
 * so usage is fully auditable and billable.
 */
class AiManager
{
    /** @var array<string, AiDriverContract> */
    private array $resolved = [];

    public function driver(?string $name = null): AiDriverContract
    {
        $name ??= (string) config('ai.driver', 'simulated');

        return $this->resolved[$name] ??= $this->build($name);
    }

    private function build(string $name): AiDriverContract
    {
        $config = config("ai.drivers.{$name}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Unknown AI driver [{$name}].");
        }

        return match ($name) {
            'simulated' => new SimulatedDriver(),
            'openai', 'anthropic' => new OpenAiDriver(
                apiKey: (string) ($config['api_key'] ?? ''),
                baseUri: (string) ($config['base_uri'] ?? ''),
                model: (string) ($config['model'] ?? ''),
                timeout: (int) ($config['timeout'] ?? 60),
            ),
            default => throw new InvalidArgumentException("Unsupported AI driver [{$name}]."),
        };
    }

    /**
     * Run a generation for a user, enforcing quota and persisting the record.
     *
     * @throws QuotaExceededException
     */
    public function generateFor(
        User $user,
        AiGenerationType $type,
        string $prompt,
        array $options = [],
        ?int $websiteId = null,
    ): AiGeneration {
        $cost = $type->credits();

        if ($user->aiCreditsRemaining() < $cost) {
            throw new QuotaExceededException(
                "You have used all of this month's AI credits. Upgrade your plan to keep generating."
            );
        }

        $request = new AiRequest($type, $prompt, $options);

        $record = AiGeneration::create([
            'user_id' => $user->id,
            'website_id' => $websiteId,
            'type' => $type->value,
            'provider' => $this->driver()->name(),
            'model' => $this->driver()->model(),
            'prompt' => mb_substr($prompt, 0, (int) config('ai.limits.prompt_max_chars', 2000)),
            'options' => $options,
            'status' => 'processing',
            'credits_used' => $cost,
        ]);

        try {
            $result = $this->driver()->generate($request);

            $record->update([
                'result' => $result->toJson(),
                'status' => 'completed',
                'tokens_used' => $result->tokensUsed,
                'duration_ms' => $result->durationMs,
                'provider' => $result->provider,
                'model' => $result->model,
            ]);
        } catch (Throwable $e) {
            Log::error('AI generation failed', ['id' => $record->id, 'error' => $e->getMessage()]);

            $record->update([
                'status' => 'failed',
                'error' => mb_substr($e->getMessage(), 0, 1000),
                'credits_used' => 0,
            ]);

            throw $e;
        }

        return $record->fresh();
    }

    /** Run a generation without persistence (previews, internal tooling). */
    public function generate(AiGenerationType $type, string $prompt, array $options = []): AiResult
    {
        return $this->driver()->generate(new AiRequest($type, $prompt, $options));
    }

    /** @return array<string, string> */
    public function availableDrivers(): array
    {
        $out = [];

        foreach ((array) config('ai.drivers', []) as $key => $config) {
            $out[$key] = (string) ($config['label'] ?? ucfirst($key));
        }

        return $out;
    }
}
