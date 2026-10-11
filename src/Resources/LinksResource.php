<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Objects\Link;

final class LinksResource extends CrudResource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'links', 'link', 'links', $prefix, $http, Link::class, $maxNetworkRetries);
    }
}
