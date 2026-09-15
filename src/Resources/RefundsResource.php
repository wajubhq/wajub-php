<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Objects\Refund;

/**
 * `GET /refunds`, `POST /refunds`, `GET /refunds/{uid}`.
 *
 * A refund cannot be updated or deleted once created. `create()` requires
 * `payment` (the payment id) and `reason`.
 */
final class RefundsResource extends Resource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'refunds', 'refund', 'refunds', $prefix, $http, Refund::class, $maxNetworkRetries);
    }
}
