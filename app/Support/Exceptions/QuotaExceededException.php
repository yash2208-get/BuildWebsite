<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Exception;

class QuotaExceededException extends Exception
{
    public function __construct(string $message = 'You have reached the limit for your current plan.')
    {
        parent::__construct($message);
    }
}
