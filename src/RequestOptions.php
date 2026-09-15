<?php

declare(strict_types=1);

namespace Wajub;

final readonly class RequestOptions
{
    /**
     * @param  array<string, string>|null  $headers
     */
    public function __construct(
        public ?string $idempotencyKey = null,
        public ?array $headers = null,
        public ?string $sync = null,
    ) {
    }
}
