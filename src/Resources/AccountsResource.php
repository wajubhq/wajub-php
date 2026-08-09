<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class AccountsResource extends CrudResource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'accounts', 'account', 'accounts', $prefix, $http, maxNetworkRetries: $maxNetworkRetries);
    }

    /** @return array<string, mixed> */
    public function regenerateToken(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/accounts/'.rawurlencode($id).'/token', null, $options),
            'account',
        );
    }
}
