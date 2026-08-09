<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class InvoicesResource extends CrudResource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'invoices', 'invoice', 'invoices', $prefix, $http, maxNetworkRetries: $maxNetworkRetries);
    }

    /** @return array<string, mixed> */
    public function send(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/invoices/'.rawurlencode($id).'/send', null, $options),
            'invoice',
        );
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function markPaid(string $id, ?array $params = null, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/invoices/'.rawurlencode($id).'/mark-paid', $params, $options),
            'invoice',
        );
    }

    /** @return array<string, mixed> */
    public function cancel(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/invoices/'.rawurlencode($id).'/cancel', null, $options),
            'invoice',
        );
    }
}
