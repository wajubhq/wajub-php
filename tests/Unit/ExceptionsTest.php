<?php

declare(strict_types=1);

use Wajub\Exception\AuthenticationException;
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\NotFoundException;
use Wajub\Exception\PermissionException;
use Wajub\Exception\RateLimitException;
use Wajub\Exception\WajubError;
use Wajub\Http\HttpUtils;

it('maps http status codes to typed exceptions', function (int $status, string $class): void {
    expect(HttpUtils::errorFromResponse($status, ['message' => 'Error']))->toBeInstanceOf($class);
})->with([
    [400, InvalidRequestException::class],
    [401, AuthenticationException::class],
    [403, PermissionException::class],
    [404, NotFoundException::class],
    [422, InvalidRequestException::class],
    [429, RateLimitException::class],
    [500, WajubError::class],
]);

it('carries retry-after on rate limit errors', function (): void {
    $error = HttpUtils::errorFromResponse(429, ['message' => 'Too many'], 60);

    expect($error)->toBeInstanceOf(RateLimitException::class)
        ->and($error->retryAfter)->toBe(60);
});
