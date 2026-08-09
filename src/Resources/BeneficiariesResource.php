<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;

final class BeneficiariesResource extends CrudResource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'beneficiaries', 'beneficiary', 'beneficiaries', $prefix, $http, maxNetworkRetries: $maxNetworkRetries);
    }
}
