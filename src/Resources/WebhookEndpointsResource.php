<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Http\HttpUtils;
use Wajub\Objects\WebhookEndpoint;
use Wajub\RequestOptions;

final class WebhookEndpointsResource extends CrudResource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'webhooks', 'endpoint', 'endpoints', $prefix, $http, WebhookEndpoint::class, $maxNetworkRetries);
    }

    /** @return array<string, mixed> */
    public function rotateSecret(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/webhooks/'.HttpUtils::pathId($id).'/rotate-secret', null, $options),
            'endpoint',
        );
    }
}
