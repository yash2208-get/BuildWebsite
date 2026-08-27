<?php

declare(strict_types=1);

namespace App\Support\Ai;

use App\Enums\AiGenerationType;

final readonly class AiRequest
{
    public function __construct(
        public AiGenerationType $type,
        public string $prompt,
        public array $options = [],
    ) {}

    public function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->options, $key, $default);
    }

    public function industry(): string
    {
        return (string) $this->option('industry', 'saas');
    }

    public function tone(): string
    {
        return (string) $this->option('tone', 'professional');
    }

    public function businessName(): string
    {
        $name = trim((string) $this->option('business_name', ''));

        return $name !== '' ? $name : str($this->prompt)->words(3, '')->title()->toString();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'prompt' => $this->prompt,
            'options' => $this->options,
        ];
    }
}
