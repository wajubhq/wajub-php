<?php

declare(strict_types=1);

namespace Wajub;

final class RequestOptions
{
    /**
     * @param  array<string, string>|null  $headers
     */
    public function __construct(
        public readonly ?string $idempotencyKey = null,
        public readonly ?array $headers = null,
        public readonly ?string $sync = null,
    ) {
    }
}
