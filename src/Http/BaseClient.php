<?php

declare(strict_types=1);

namespace Wajub\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Wajub\Exception\ApiConnectionException;
use Wajub\Exception\WajubError;
use Wajub\RequestOptions;
use Wajub\Version;

class BaseClient
{
    protected Client $http;

    public function __construct(
        protected readonly string $apiKey,
        protected readonly string $baseUrl,
        protected readonly ?string $idempotencyKeyPrefix = 'wajub',
        ?Client $http = null,
        protected readonly ?int $maxNetworkRetries = null,
    ) {
        $this->http = $http ?? self::createHttpClient($this->baseUrl);
    }

    public static function createHttpClient(string $baseUrl, ?float $timeout = null): Client
    {
        return new Client([
            'base_uri' => $baseUrl,
            'http_errors' => false,
            'timeout' => $timeout ?? 30,
            'headers' => [
                'User-Agent' => 'wajub-php/'.Version::VERSION,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function get(string $path, ?array $params = null): array
    {
        return $this->request('GET', $path, null, null, $params);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    public function post(string $path, ?array $body = null, ?RequestOptions $options = null): array
    {
        return $this->request('POST', $path, $body, $options);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    public function put(string $path, ?array $body = null, ?RequestOptions $options = null): array
    {
        return $this->request('PUT', $path, $body, $options);
    }

    /**
     * @return array<string, mixed>
     */
    public function del(string $path, ?RequestOptions $options = null): array
    {
        return $this->request('DELETE', $path, null, $options);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, mixed>|null  $query
     * @return array<string, mixed>
     */
    public function request(
        string $method,
        string $path,
        ?array $body = null,
        ?RequestOptions $options = null,
        ?array $query = null,
    ): array {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => HttpUtils::normalizeApiKey($this->apiKey),
        ];

        if ($options?->headers) {
            $headers = array_merge($headers, $options->headers);
        }

        if ($options?->sync) {
            $headers['X-Sync'] = $options->sync;
        }

        $idempotencyKey = $options?->idempotencyKey;
        if ($idempotencyKey === null && ! in_array($method, ['GET', 'DELETE'], true)) {
            $idempotencyKey = HttpUtils::createIdempotencyKey($this->idempotencyKeyPrefix ?? 'wajub');
        }

        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $requestOptions = ['headers' => $headers];

        if ($query !== null) {
            $requestOptions['query'] = array_filter(
                $query,
                static fn ($value): bool => $value !== null,
            );
        }

        if ($body !== null) {
            $headers['Content-Type'] = 'application/json';
            $requestOptions['headers'] = $headers;
            $requestOptions['json'] = $body;
        }

        $attempt = 0;
        $maxRetries = $this->maxNetworkRetries ?? RetryPolicy::maxRetries();

        while (true) {
            try {
                $response = $this->http->request($method, $path, $requestOptions);
            } catch (GuzzleException $e) {
                if ($attempt < $maxRetries) {
                    $attempt++;
                    usleep(RetryPolicy::delayMicroseconds($attempt));

                    continue;
                }

                throw new ApiConnectionException($e->getMessage(), 'network_error');
            }

            $status = $response->getStatusCode();
            $rawBody = (string) $response->getBody();
            $decoded = json_decode($rawBody, true);
            $data = is_array($decoded) ? $decoded : [];

            if ($status >= 200 && $status < 300) {
                if (trim($rawBody) !== '' && ! is_array($decoded)) {
                    throw new WajubError('Wajub: the API returned a non-JSON body.', 'invalid_response', $status);
                }

                return $data;
            }

            if (RetryPolicy::shouldRetryStatus($status) && $attempt < $maxRetries) {
                $attempt++;
                usleep(RetryPolicy::delayMicroseconds($attempt, $response));

                continue;
            }

            $retryAfter = RetryPolicy::parseRetryAfter($response);
            throw HttpUtils::errorFromResponse($status, $data, $retryAfter);
        }
    }
}
