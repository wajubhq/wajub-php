<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Wajub\Crypto;
use Wajub\Version;

const API_KEY = 'sk_test.unit_test_key';
const BASE_URL = Version::API_URL;

/**
 * @param array<int, ResponseInterface|\Throwable|callable> $responses
 * @return array{0: Client, 1: MockHandler}
 */
function mockClientWithCapture(array $responses): array
{
    $mock = new MockHandler($responses);

    $client = new Client([
        'handler' => $mock,
        'base_uri' => BASE_URL,
        'http_errors' => false,
    ]);

    return [$client, $mock];
}

/**
 * @param array<int, ResponseInterface|\Throwable|callable> $responses
 */
function mockHttpClient(array $responses): Client
{
    return mockClientWithCapture($responses)[0];
}

/**
 * @param  array<string, mixed>  $body
 */
function jsonResponse(array $body, int $status = 200): Response
{
    return new Response($status, [], json_encode($body, JSON_THROW_ON_ERROR));
}

function signWebhook(string $secret, string $payload, int $timestamp): string
{
    return 'v1='.Crypto::hmacSha256($secret, "{$timestamp}.{$payload}");
}
