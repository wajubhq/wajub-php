<?php

declare(strict_types=1);

namespace Wajub\Tests;

use GuzzleHttp\Psr7\Response;
use Wajub\Exception\AuthenticationException;
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\RateLimitException;
use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;

final class ExceptionsTest extends TestCase
{
    public function test_error_from_response_maps_status_codes(): void
    {
        $this->assertInstanceOf(
            AuthenticationException::class,
            HttpUtils::errorFromResponse(401, ['message' => 'Unauthorized']),
        );
        $this->assertInstanceOf(
            InvalidRequestException::class,
            HttpUtils::errorFromResponse(422, ['message' => 'Validation failed']),
        );
        $rateLimit = HttpUtils::errorFromResponse(429, ['message' => 'Too many'], 60);
        $this->assertInstanceOf(RateLimitException::class, $rateLimit);
        $this->assertSame(60, $rateLimit->retryAfter);
    }

    public function test_base_client_throws_invalid_request_exception(): void
    {
        $http = $this->mockHttpClient([
            new Response(422, [], json_encode(['message' => 'Bad request', 'code' => 'validation_error'])),
        ]);
        $client = new BaseClient(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $this->expectException(InvalidRequestException::class);
        $client->post('/payments', ['amount' => -1]);
    }
}
