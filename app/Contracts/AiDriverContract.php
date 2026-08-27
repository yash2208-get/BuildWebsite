<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Support\Ai\AiRequest;
use App\Support\Ai\AiResult;

interface AiDriverContract
{
    /**
     * Execute a generation request and return a normalised result payload.
     */
    public function generate(AiRequest $request): AiResult;

    /**
     * Machine name of the driver (e.g. "simulated", "openai").
     */
    public function name(): string;

    /**
     * Model identifier used for the last/next call.
     */
    public function model(): string;
}
