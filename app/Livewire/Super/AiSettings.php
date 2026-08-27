<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\ActivityLog;
use App\Models\AiGeneration;
use App\Services\Ai\AiManager;
use App\Services\Platform\SettingsService;
use Livewire\Attributes\Layout;
use Throwable;

#[Layout('layouts.app')]
class AiSettings extends BaseComponent
{
    public string $driver = 'simulated';
    public string $openaiKey = '';
    public string $openaiModel = 'gpt-4o-mini';
    public string $anthropicKey = '';
    public float $temperature = 0.7;
    public int $maxTokens = 4000;
    public int $promptMaxChars = 2000;
    public bool $aiEnabled = true;
    public int $freeCredits = 25;
    public string $testPrompt = 'A modern coffee subscription for remote workers';
    public ?string $testResult = null;
    public bool $testing = false;

    public function mount(): void
    {
        $s = app(SettingsService::class);

        $this->driver = $s->get('ai_driver', config('ai.driver', 'simulated'));
        $this->openaiKey = (string) $s->get('ai_openai_key', '');
        $this->openaiModel = $s->get('ai_openai_model', 'gpt-4o-mini');
        $this->anthropicKey = (string) $s->get('ai_anthropic_key', '');
        $this->temperature = (float) $s->get('ai_temperature', 0.7);
        $this->maxTokens = (int) $s->get('ai_max_tokens', 4000);
        $this->promptMaxChars = (int) $s->get('ai_prompt_max_chars', 2000);
        $this->aiEnabled = (bool) $s->get('ai_enabled', true);
        $this->freeCredits = (int) $s->get('ai_free_credits', 25);
    }

    public function save(): void
    {
        $this->authorize('manage-settings');

        $this->validate([
            'driver' => ['required', 'in:simulated,openai,anthropic'],
            'openaiModel' => ['required', 'string', 'max:60'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'maxTokens' => ['required', 'integer', 'min:256', 'max:128000'],
            'promptMaxChars' => ['required', 'integer', 'min:100', 'max:20000'],
            'freeCredits' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $s = app(SettingsService::class);

        foreach ([
            'ai_driver' => $this->driver,
            'ai_openai_key' => $this->openaiKey,
            'ai_openai_model' => $this->openaiModel,
            'ai_anthropic_key' => $this->anthropicKey,
            'ai_temperature' => $this->temperature,
            'ai_max_tokens' => $this->maxTokens,
            'ai_prompt_max_chars' => $this->promptMaxChars,
            'ai_enabled' => $this->aiEnabled,
            'ai_free_credits' => $this->freeCredits,
        ] as $key => $value) {
            $s->set($key, $value);
        }

        $s->flushCache();

        ActivityLog::record('updated', "AI engine settings updated (driver: {$this->driver})");
        $this->notifySuccess('AI settings saved.');
    }

    public function runTest(): void
    {
        $this->testing = true;
        $this->testResult = null;

        try {
            $result = app(AiManager::class)->driver($this->driver)->generate(
                new \App\Support\Ai\AiRequest(
                    type: \App\Enums\AiGenerationType::Content,
                    prompt: $this->testPrompt,
                    options: ['content_type' => 'headline', 'tone' => 'professional'],
                )
            );

            $this->testResult = json_encode($result->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $this->notifySuccess('Test generation succeeded.');
        } catch (Throwable $e) {
            $this->testResult = 'Error: '.$e->getMessage();
            $this->notifyError('Test failed — check the driver configuration.');
        } finally {
            $this->testing = false;
        }
    }

    public function render()
    {
        return view('livewire.super.ai-settings', [
            'stats' => [
                'generations' => AiGeneration::count(),
                'completed' => AiGeneration::where('status', 'completed')->count(),
                'failed' => AiGeneration::where('status', 'failed')->count(),
                'credits' => (int) AiGeneration::sum('credits_used'),
                'tokens' => (int) AiGeneration::sum('tokens_used'),
                'avgMs' => (int) AiGeneration::where('status', 'completed')->avg('duration_ms'),
            ],
            'byType' => AiGeneration::selectRaw('type, COUNT(*) as total, SUM(credits_used) as credits')
                ->groupBy('type')->orderByDesc('total')->get(),
        ])->layoutData($this->layoutData('AI Engine Settings'));
    }
}
