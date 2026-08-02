<?php

declare(strict_types=1);

namespace Wajub\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected const BASE_URL = \Wajub\Version::API_URL;

    protected const API_KEY = 'sk_test.unit_test_key';

    /**
     * @param  array<int, \Psr\Http\Message\ResponseInterface|\Throwable|callable>  $responses
     */
    protected function mockHttpClient(array $responses): Client
    {
        return $this->mockClientWithCapture($responses)[0];
    }

    /**
     * @param  array<int, \Psr\Http\Message\ResponseInterface|\Throwable|callable>  $responses
     * @return array{0: Client, 1: MockHandler}
     */
    protected function mockClientWithCapture(array $responses): array
    {
        $mock = new MockHandler($responses);

        $client = new Client([
            'handler' => $mock,
            'base_uri' => self::BASE_URL,
            'http_errors' => false,
        ]);

        return [$client, $mock];
    }
}
