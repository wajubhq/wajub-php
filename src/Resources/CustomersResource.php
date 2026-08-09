<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Http\HttpUtils;
use Wajub\Objects\Customer;
use Wajub\RequestOptions;

final class CustomersResource extends CrudResource
{
    public function __construct(string $apiKey, string $baseUrl, ?string $prefix = 'wajub', ?Client $http = null, ?int $maxNetworkRetries = null)
    {
        parent::__construct($apiKey, $baseUrl, 'customers', 'customer', 'customers', $prefix, $http, Customer::class, $maxNetworkRetries);
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function block(string $id, ?array $params = null, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/customers/'.rawurlencode($id).'/block', $params, $options),
            'customer',
        );
    }

    /** @return array<string, mixed> */
    public function unblock(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/customers/'.rawurlencode($id).'/unblock', null, $options),
            'customer',
        );
    }

    /** @return array<string, mixed> */
    public function activate(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/customers/'.rawurlencode($id).'/activate', null, $options),
            'customer',
        );
    }

    /** @return array<string, mixed> */
    public function deactivate(string $id, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/customers/'.rawurlencode($id).'/deactivate', null, $options),
            'customer',
        );
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null} */
    public function listTaxIds(string $customerId): array
    {
        return HttpUtils::pickList(
            $this->get('/customers/'.rawurlencode($customerId).'/tax_ids'),
            'tax_ids',
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createTaxId(string $customerId, array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/customers/'.rawurlencode($customerId).'/tax_ids', $params, $options),
            'tax_id',
        );
    }

    public function deleteTaxId(string $customerId, string $taxId, ?RequestOptions $options = null): void
    {
        $this->del(
            '/customers/'.rawurlencode($customerId).'/tax_ids/'.rawurlencode($taxId),
            $options,
        );
    }
}
