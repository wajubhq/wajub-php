<?php

declare(strict_types=1);

namespace Wajub\Http;

use Psr\Http\Message\ResponseInterface;

final class RetryPolicy
{
    private const int MAX_RETRIES = 2;

    /**
     * Longest pause between two attempts, whatever `Retry-After` says. A PHP-FPM
     * worker must never be parked for minutes on a single API call.
     */
    private const int MAX_DELAY_SECONDS = 10;

    /** @var int[] */
    private const array RETRYABLE_STATUS = [429, 500, 502, 503, 504];

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
        if ($response instanceof ResponseInterface) {
            $retryAfter = self::parseRetryAfter($response);

            if ($retryAfter !== null) {
                return min($retryAfter, self::MAX_DELAY_SECONDS) * 1_000_000;
            }
        }

        $base = 500_000 * (2 ** ($attempt - 1));
        $jitter = random_int(50, 100) / 100;

        return (int) ($base * $jitter);
    }

    /**
     * The `409` the API answers while a request with the same `Idempotency-Key`
     * is still running (`errors.idempotency_key`). A call sent again after a
     * timeout can arrive before the first has finished: its answer is on the
     * way, so the call is worth repeating with the same key. Every other `409`
     * is a real refusal.
     *
     * @param  array<string, mixed>  $body
     */
    public static function isIdempotencyKeyInProgress(int $status, array $body): bool
    {
        return $status === 409
            && is_array($body['errors'] ?? null)
            && array_key_exists('idempotency_key', $body['errors']);
    }

    /**
     * The pause before asking again about a key still being processed: 2, 4,
     * then 8 seconds, never more than the usual cap.
     */
    public static function idempotencyConflictDelayMicroseconds(int $attempt): int
    {
        return min(2 ** max(1, $attempt), self::MAX_DELAY_SECONDS) * 1_000_000;
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
