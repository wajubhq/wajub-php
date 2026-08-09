<?php

declare(strict_types=1);

namespace Wajub\Http;

use Psr\Http\Message\ResponseInterface;

final class RetryPolicy
{
    private const MAX_RETRIES = 2;

    /** @var int[] */
    private const RETRYABLE_STATUS = [429, 500, 502, 503, 504];

    public static function maxRetries(): int
    {
        return self::MAX_RETRIES;
    }

    public static function shouldRetryStatus(int $status): bool
    {
        return in_array($status, self::RETRYABLE_STATUS, true);
    }

    public static function delayMicroseconds(int $attempt, ?ResponseInterface $response = null): int
    {
        if ($response !== null) {
            $retryAfter = self::parseRetryAfter($response);
            if ($retryAfter !== null) {
                return $retryAfter * 1_000_000;
            }
        }

        $base = 500_000 * (2 ** ($attempt - 1));
        $jitter = 0.5 + (mt_rand() / mt_getrandmax()) * 0.5;

        return (int) ($base * $jitter);
    }

    public static function parseRetryAfter(ResponseInterface $response): ?int
    {
        $header = trim($response->getHeaderLine('Retry-After'));
        if ($header === '') {
            return null;
        }

        if (ctype_digit($header)) {
            return (int) $header;
        }

        $timestamp = strtotime($header);

        return $timestamp !== false ? max(0, $timestamp - time()) : null;
    }
}
