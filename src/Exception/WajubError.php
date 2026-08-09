<?php

declare(strict_types=1);

namespace Wajub\Exception;

use Exception;

class WajubError extends Exception
{
    /**
     * @param  array<string, string|string[]>|null  $errors
     * @param  array<string, mixed>|null  $raw
     */
    public function __construct(
        string $message,
        public readonly string $errorCode = 'api_error',
        public readonly ?int $httpStatus = null,
        public readonly ?array $errors = null,
        public readonly ?array $raw = null,
    ) {
        parent::__construct($message);
    }
}
