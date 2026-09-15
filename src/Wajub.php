<?php

declare(strict_types=1);

namespace Wajub;

use GuzzleHttp\Client;
use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\Resources\AccountsResource;
use Wajub\Resources\BalanceResource;
use Wajub\Resources\BeneficiariesResource;
use Wajub\Resources\CustomersResource;
use Wajub\Resources\DisputesResource;
use Wajub\Resources\EventsResource;
use Wajub\Resources\GlobalResource;
use Wajub\Resources\IdentityResource;
use Wajub\Resources\InvoicesResource;
use Wajub\Resources\LinksResource;
use Wajub\Resources\ListenResource;
use Wajub\Resources\PaymentsResource;
use Wajub\Resources\RefundsResource;
use Wajub\Resources\ShieldResource;
use Wajub\Resources\TaxResource;
use Wajub\Resources\TransfersResource;
use Wajub\Resources\WebhookEndpointsResource;

/**
 * Wajub server SDK — Stripe-like entry point for PHP 8.4+.
 *
 * ```php
 * $wajub = new Wajub(['api_key' => env('WAJUB_API_KEY')]);
 * $payment = $wajub->payments->create([
 *     'amount' => 15000,
 *     'currency' => 'XAF',
 *     'email' => 'buyer@example.com',
 *     'callback' => 'https://shop.example.com/complete',
 * ]);
 * ```
 */
final readonly class Wajub
{
    public GlobalResource $global;
    public PaymentsResource $payments;
    public CustomersResource $customers;
    public RefundsResource $refunds;
    public TransfersResource $transfers;
    public BeneficiariesResource $beneficiaries;
    public LinksResource $links;
    public BalanceResource $balance;
    public EventsResource $events;
    public AccountsResource $accounts;
    public WebhookEndpointsResource $webhookEndpoints;
    public InvoicesResource $invoices;
    public DisputesResource $disputes;
    public IdentityResource $identity;
    public TaxResource $tax;
    public ShieldResource $shield;
    public ListenResource $listen;
    public Webhooks $webhooks;

    /**
     * @param  array{
     *     api_key?: string,
     *     webhook_secret?: string,
     *     idempotency_key_prefix?: string,
     *     http_client?: Client,
     *     max_network_retries?: int,
     *     timeout?: float
     * }  $config
     */
    public function __construct(array $config)
    {
        $apiKey = HttpUtils::normalizeApiKey($config['api_key'] ?? '');
        if ($apiKey === '') {
            throw new \InvalidArgumentException('Wajub: api_key is required');
        }

        $baseUrl = HttpUtils::defaultApiUrl();
        $prefix = $config['idempotency_key_prefix'] ?? 'wajub';
        $http = $config['http_client'] ?? BaseClient::createHttpClient($baseUrl, $config['timeout'] ?? null);
        $maxNetworkRetries = $config['max_network_retries'] ?? null;

        $this->global = new GlobalResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->payments = new PaymentsResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->customers = new CustomersResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->refunds = new RefundsResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->transfers = new TransfersResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->beneficiaries = new BeneficiariesResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->links = new LinksResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->balance = new BalanceResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->events = new EventsResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->accounts = new AccountsResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->webhookEndpoints = new WebhookEndpointsResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->invoices = new InvoicesResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->disputes = new DisputesResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->identity = new IdentityResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->tax = new TaxResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->shield = new ShieldResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);
        $this->listen = new ListenResource($apiKey, $baseUrl, $prefix, $http, $maxNetworkRetries);

        $webhookSecret = $config['webhook_secret'] ?? getenv('WAJUB_WEBHOOK_SECRET') ?: '';
        $this->webhooks = new Webhooks($webhookSecret);
    }
}
