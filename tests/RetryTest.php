<?php

declare(strict_types=1);

namespace Wajub\Tests;

use GuzzleHttp\Psr7\Response;
use Wajub\Http\BaseClient;

final class RetryTest extends TestCase
{
    public function test_retries_on_503_then_succeeds(): void
    {
        $http = $this->mockHttpClient([
            new Response(503, [], json_encode(['message' => 'Unavailable'])),
            new Response(200, [], json_encode(['ok' => true])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $result = $client->get('/ping');

        $this->assertTrue($result['ok']);
    }
}
