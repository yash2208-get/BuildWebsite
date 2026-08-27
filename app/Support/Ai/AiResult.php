<?php

declare(strict_types=1);

namespace App\Support\Ai;

final readonly class AiResult
{
    public function __construct(
        public array $data,
        public string $provider = 'simulated',
        public string $model = 'aurora-compose-1',
        public int $tokensUsed = 0,
        public int $durationMs = 0,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}
