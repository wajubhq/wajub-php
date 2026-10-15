<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\WajubError;
use Wajub\Http\BaseClient;
use Wajub\Http\RetryPolicy;
use Wajub\RequestOptions;

it('sends the raw api key in the authorization header', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['ok' => true])]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->get('/ping');

    $request = $mock->getLastRequest();
    expect($request)->not->toBeNull()
        ->and($request->getHeaderLine('Authorization'))->toBe(API_KEY)
        ->and($request->getHeaderLine('Accept'))->toBe('application/json');
});

it('adds an idempotency key to post requests by default', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['transaction' => ['id' => 'pay_1']])]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->post('/payments', ['amount' => 1000]);

    expect($mock->getLastRequest()?->getHeaderLine('Idempotency-Key'))->toStartWith('wajub-');
});

it('honors a custom idempotency key and the sync header', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['transaction' => ['id' => 'pay_1']])]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->post('/payments', ['amount' => 1000], new RequestOptions(
        idempotencyKey: 'custom-key-123',
        sync: 'acct_sync_ref',
    ));

    $request = $mock->getLastRequest();
    expect($request?->getHeaderLine('Idempotency-Key'))->toBe('custom-key-123')
        ->and($request?->getHeaderLine('X-Sync'))->toBe('acct_sync_ref');
});

it('does not send an idempotency key on get requests', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['transactions' => []])]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->get('/payments');

    expect($mock->getLastRequest()?->getHeaderLine('Idempotency-Key'))->toBe('');
});

it('passes query parameters on get requests', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['transactions' => [], 'meta' => []])]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->get('/payments', ['page' => 2, 'status' => 'pending']);

    expect((string) $mock->getLastRequest()?->getUri())
        ->toContain('page=2')
        ->toContain('status=pending');
});

it('throws a typed error on non-success responses', function (): void {
    $http = mockHttpClient([jsonResponse(['message' => 'Unprocessable', 'code' => 'validation_error'], 422)]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->post('/payments', ['amount' => -1]);
})->throws(InvalidRequestException::class, 'Unprocessable');

it('retries on 503 and then succeeds', function (): void {
    $http = mockHttpClient([
        jsonResponse(['message' => 'Unavailable'], 503),
        jsonResponse(['ok' => true]),
    ]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    expect($client->get('/ping')['ok'])->toBeTrue();
});

it('caps the retry-after pause at ten seconds', function (): void {
    $response = new Response(429, ['Retry-After' => '3600']);

    expect(RetryPolicy::delayMicroseconds(1, $response))->toBe(10_000_000)
        ->and(RetryPolicy::delayMicroseconds(1, new Response(429, ['Retry-After' => '2'])))->toBe(2_000_000);
});

it('rejects a successful response whose body is not json', function (): void {
    $http = mockHttpClient([new Response(200, ['Content-Type' => 'text/html'], '<html>proxy</html>')]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $client->get('/ping');
})->throws(WajubError::class, 'non-JSON');

it('accepts an empty body on success', function (): void {
    $http = mockHttpClient([new Response(204)]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    expect($client->del('/customers/cus_1'))->toBe([]);
});

/**
 * A call sent again after a timeout can reach the API while the first one is
 * still running: the API answers 409 with errors.idempotency_key. The answer
 * is on its way, so the client asks again with the same key.
 */
function idempotencyKeyInProgress(): Response
{
    return jsonResponse([
        'code' => 409,
        'status' => 'Conflict',
        'message' => 'A request with this Idempotency-Key is still being processed.',
        'errors' => ['idempotency_key' => ['A request with this Idempotency-Key is still being processed.']],
    ], 409);
}

it('asks again with the same key while that key is still being processed', function (): void {
    $keys = [];
    $capture = static function (Response $response) use (&$keys): callable {
        return static function ($request) use ($response, &$keys): Response {
            $keys[] = $request->getHeaderLine('Idempotency-Key');

            return $response;
        };
    };
    $http = mockHttpClient([
        $capture(idempotencyKeyInProgress()),
        $capture(jsonResponse(['transaction' => ['id' => 'trx_1']], 201)),
    ]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    $result = $client->post('/payments', ['amount' => 1000], new RequestOptions(idempotencyKey: 'ORDER-42'));

    expect($result['transaction']['id'])->toBe('trx_1')
        ->and($keys)->toBe(['ORDER-42', 'ORDER-42']);
});

it('raises that 409 once the retries are spent', function (): void {
    $http = mockHttpClient([idempotencyKeyInProgress(), idempotencyKeyInProgress()]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http, maxNetworkRetries: 1);

    $client->post('/payments', ['amount' => 1000]);
})->throws(WajubError::class, 'still being processed');

it('raises any other 409 at once', function (): void {
    [$http, $mock] = mockClientWithCapture([
        jsonResponse(['code' => 409, 'error_code' => 'payment_in_progress', 'message' => 'A charge is already in progress for this payment.'], 409),
        jsonResponse(['ok' => true]),
    ]);
    $client = new BaseClient(API_KEY, BASE_URL, 'wajub', $http);

    expect(fn (): array => $client->post('/payments/trx_1', ['channel' => 'cm.mtn']))->toThrow(WajubError::class, 'already in progress')
        ->and($mock->count())->toBe(1);
});

it('waits 2, 4 then 8 seconds between those retries, never more than the cap', function (): void {
    expect(RetryPolicy::idempotencyConflictDelayMicroseconds(1))->toBe(2_000_000)
        ->and(RetryPolicy::idempotencyConflictDelayMicroseconds(2))->toBe(4_000_000)
        ->and(RetryPolicy::idempotencyConflictDelayMicroseconds(3))->toBe(8_000_000)
        ->and(RetryPolicy::idempotencyConflictDelayMicroseconds(4))->toBe(10_000_000);
});
