<?php

declare(strict_types=1);

namespace Wajub\Exception;

final class RateLimitException extends WajubError
{
    public function __construct(
        string $message,
        string $errorCode = 'rate_limit',
        ?int $httpStatus = 429,
        ?array $errors = null,
        ?array $raw = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $errorCode, $httpStatus, $errors, $raw);
    }
}
