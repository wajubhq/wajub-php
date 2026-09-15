<?php

declare(strict_types=1);

use Wajub\Exception\InvalidRequestException;
use Wajub\Http\HttpUtils;

it('strips the bearer prefix from api keys', function (string $raw, string $expected): void {
    expect(HttpUtils::normalizeApiKey($raw))->toBe($expected);
})->with([
    ['Bearer sk_test.abc', 'sk_test.abc'],
    ['bearer pk_test.abc', 'pk_test.abc'],
    ['sk_test.abc', 'sk_test.abc'],
]);

it('picks the first matching resource key', function (): void {
    $body = ['transaction' => ['id' => 'pay_123', 'amount' => 5000]];

    expect(HttpUtils::pickResource($body, 'transaction')['id'])->toBe('pay_123');
});

it('falls back to the body when no resource key matches', function (): void {
    expect(HttpUtils::pickResource(['id' => 'pay_123'], 'transaction')['id'])->toBe('pay_123');
});

it('extracts a plural list key and its meta', function (): void {
    $picked = HttpUtils::pickList([
        'refunds' => [['id' => 'ref_1']],
        'meta' => ['current_page' => 1, 'last_page' => 1],
    ], 'refunds');

    expect($picked['data'][0]['id'])->toBe('ref_1')
        ->and($picked['meta']['current_page'])->toBe(1);
});

it('falls back to the data key when no list key matches', function (): void {
    $picked = HttpUtils::pickList(['data' => [['id' => 'x']], 'meta' => ['total' => 1]], 'missing');

    expect($picked['data'][0]['id'])->toBe('x');
});

it('prefixes generated idempotency keys', function (): void {
    expect(HttpUtils::createIdempotencyKey())->toStartWith('wajub-')
        ->and(HttpUtils::createIdempotencyKey('custom'))->toStartWith('custom-');
});

it('builds a typed error from an error response', function (): void {
    $error = HttpUtils::errorFromResponse(422, [
        'message' => 'Validation failed',
        'code' => 'validation_error',
        'errors' => ['amount' => ['Must be positive']],
    ]);

    expect($error)->toBeInstanceOf(InvalidRequestException::class)
        ->and($error->getMessage())->toBe('Validation failed')
        ->and($error->errorCode)->toBe('validation_error')
        ->and($error->httpStatus)->toBe(422)
        ->and($error->errors['amount'])->toBe('Must be positive');
});
