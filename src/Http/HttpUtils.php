<?php

declare(strict_types=1);

namespace Wajub\Http;

use Wajub\Exception\ApiConnectionException;
use Wajub\Exception\AuthenticationException;
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\NotFoundException;
use Wajub\Exception\PermissionException;
use Wajub\Exception\RateLimitException;
use Wajub\Exception\WajubError;
use Wajub\Version;

final class HttpUtils
{
    public static function normalizeApiKey(string $raw): string
    {
        $key = trim($raw);
        if (preg_match('/^bearer\s+/i', $key)) {
            $key = trim((string) preg_replace('/^bearer\s+/i', '', $key));
        }

        return $key;
    }

    public static function defaultApiUrl(): string
    {
        return Version::API_URL;
    }

    public static function createIdempotencyKey(string $prefix = 'wajub'): string
    {
        return $prefix.'-'.self::uuid();
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  string[]  $keys
     * @return array<string, mixed>
     */
    public static function pickResource(array $body, string ...$keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $body) && $body[$key] !== null) {
                return (array) $body[$key];
            }
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  string[]  $keys
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public static function pickList(array $body, string ...$keys): array
    {
        foreach ([...$keys, 'items'] as $key) {
            if (array_key_exists($key, $body) && $body[$key] !== null) {
                return [
                    'data' => (array) $body[$key],
                    'meta' => isset($body['meta']) ? (array) $body['meta'] : null,
                ];
            }
        }

        return [
            'data' => isset($body['data']) ? (array) $body['data'] : [],
            'meta' => isset($body['meta']) ? (array) $body['meta'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function errorFromResponse(int $status, array $body, ?int $retryAfter = null): WajubError
    {
        $message = is_string($body['message'] ?? null)
            ? $body['message']
            : "Request failed ({$status})";

        $code = is_string($body['error_code'] ?? null)
            ? $body['error_code']
            : (is_string($body['code'] ?? null) ? $body['code'] : "http_{$status}");

        $errors = null;
        if (isset($body['errors']) && is_array($body['errors'])) {
            $errors = [];
            foreach ($body['errors'] as $field => $value) {
                $errors[(string) $field] = is_array($value) ? (string) ($value[0] ?? '') : (string) $value;
            }
        }

        return match (true) {
            $status === 401 => new AuthenticationException($message, $code, $status, $errors, $body),
            $status === 403 => new PermissionException($message, $code, $status, $errors, $body),
            $status === 404 => new NotFoundException($message, $code, $status, $errors, $body),
            $status === 429 => new RateLimitException($message, $code, $status, $errors, $body, $retryAfter),
            $status === 400, $status === 422 => new InvalidRequestException($message, $code, $status, $errors, $body),
            default => new WajubError($message, $code, $status, $errors, $body),
        };
    }

    private static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
