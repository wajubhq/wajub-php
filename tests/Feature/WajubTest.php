<?php

declare(strict_types=1);

use Wajub\Exception\WebhookSignatureVerificationError;
use Wajub\Wajub;

it('requires an api key', function (array $config): void {
    new Wajub($config);
})->with([
    'missing' => [[]],
    'empty' => [['api_key' => '']],
])->throws(InvalidArgumentException::class, 'api_key is required');

it('accepts public and restricted keys', function (string $key): void {
    expect(new Wajub(['api_key' => $key]))->toBeInstanceOf(Wajub::class);
})->with(['pk_test.public', 'rk_test.restricted']);

it('exposes every resource', function (string $resource): void {
    expect((new Wajub(['api_key' => API_KEY]))->{$resource})->not->toBeNull();
})->with([
    'global', 'payments', 'customers', 'refunds', 'transfers', 'beneficiaries', 'links', 'balance',
    'events', 'accounts', 'webhookEndpoints', 'invoices', 'disputes', 'identity', 'tax', 'shield',
    'listen', 'webhooks',
]);

it('verifies webhooks through the entry point', function (): void {
    $secret = 'whsec_test_sdk_entry';
    $payload = '{"type":"payment.succeeded","data":{"id":"trx_abc"}}';
    $timestamp = time();

    $wajub = new Wajub(['api_key' => API_KEY, 'webhook_secret' => $secret]);

    $event = $wajub->webhooks->constructEvent($payload, signWebhook($secret, $payload, $timestamp), (string) $timestamp);

    expect($event['type'])->toBe('payment.succeeded')
        ->and($event['data']['id'])->toBe('trx_abc');
});

it('rejects a wrong webhook signature through the entry point', function (): void {
    $wajub = new Wajub(['api_key' => API_KEY, 'webhook_secret' => 'whsec_test_sdk_entry']);

    $wajub->webhooks->constructEvent(
        '{"type":"payment.succeeded"}',
        'v1=deadbeef00000000000000000000000000000000000000000000000000000000',
        (string) time(),
    );
})->throws(WebhookSignatureVerificationError::class);

it('requires a webhook secret to verify events', function (): void {
    $wajub = new Wajub(['api_key' => API_KEY]);

    $wajub->webhooks->constructEvent('{}', 'v1=abc', (string) time());
})->throws(InvalidArgumentException::class, 'webhookSecret is required');
