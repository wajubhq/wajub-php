<?php

declare(strict_types=1);

namespace Wajub;

final class Crypto
{
    public static function hmacSha256(string $key, string $data): string
    {
        return hash_hmac('sha256', $data, $key);
    }

    public static function timingSafeEqual(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }
}
