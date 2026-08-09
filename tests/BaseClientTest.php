<?php

declare(strict_types=1);

namespace Wajub\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Wajub\Exception\InvalidRequestException;
use Wajub\Http\BaseClient;
use Wajub\RequestOptions;

final class BaseClientTest extends TestCase
{
    public function test_sends_raw_api_key_in_authorization_header(): void
    {
        [$http, $mock] = $this->mockClientWithCapture([
            new Response(200, [], json_encode(['ok' => true])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $client->get('/ping');

        $request = $mock->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame(self::API_KEY, (string) $request->getHeaderLine('Authorization'));
        $this->assertSame('application/json', (string) $request->getHeaderLine('Accept'));
    }

    public function test_post_includes_idempotency_key_by_default(): void
    {
        [$http, $mock] = $this->mockClientWithCapture([
            new Response(200, [], json_encode(['transaction' => ['id' => 'pay_1']])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $client->post('/payments', ['amount' => 1000]);

        $request = $mock->getLastRequest();
        $this->assertNotNull($request);
        $key = (string) $request->getHeaderLine('Idempotency-Key');
        $this->assertStringStartsWith('wajub-', $key);
    }

    public function test_post_honors_custom_idempotency_key_and_sync_header(): void
    {
        [$http, $mock] = $this->mockClientWithCapture([
            new Response(200, [], json_encode(['transaction' => ['id' => 'pay_1']])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $client->post('/payments', ['amount' => 1000], new RequestOptions(
            idempotencyKey: 'custom-key-123',
            sync: 'acct_sync_ref',
        ));

        $request = $mock->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('custom-key-123', (string) $request->getHeaderLine('Idempotency-Key'));
        $this->assertSame('acct_sync_ref', (string) $request->getHeaderLine('X-Sync'));
    }

    public function test_get_does_not_send_idempotency_key(): void
    {
        [$http, $mock] = $this->mockClientWithCapture([
            new Response(200, [], json_encode(['transactions' => []])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $client->get('/payments');

        $request = $mock->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('', (string) $request->getHeaderLine('Idempotency-Key'));
    }

    public function test_throws_wajub_error_on_non_success_status(): void
    {
        [$http] = $this->mockClientWithCapture([
            new Response(422, [], json_encode([
                'message' => 'Unprocessable',
                'code' => 'validation_error',
            ])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $this->expectException(InvalidRequestException::class);
        $client->post('/payments', ['amount' => -1]);
    }

    public function test_get_passes_query_parameters(): void
    {
        [$http, $mock] = $this->mockClientWithCapture([
            new Response(200, [], json_encode(['transactions' => [], 'meta' => []])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $client->get('/payments', ['page' => 2, 'status' => 'pending']);

        $request = $mock->getLastRequest();
        $this->assertNotNull($request);
        $uri = (string) $request->getUri();
        $this->assertStringContainsString('page=2', $uri);
        $this->assertStringContainsString('status=pending', $uri);
    }
}
