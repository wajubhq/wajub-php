<?php

declare(strict_types=1);

use Wajub\Objects\Refund;
use Wajub\Resources\BalanceResource;
use Wajub\Resources\DisputesResource;
use Wajub\Resources\ListenResource;
use Wajub\Resources\PaymentsResource;
use Wajub\Resources\RefundsResource;
use Wajub\Resources\ShieldResource;
use Wajub\Resources\TaxResource;
use Wajub\Resources\TransfersResource;

it('unwraps the transaction and authorization fields on payment create', function (): void {
    $http = mockHttpClient([jsonResponse([
        'transaction' => ['id' => 'pay_123', 'status' => 'pending'],
        'authorization_token' => 'tok_abc',
        'authorization_url' => 'https://checkout.test/pay/pay_123',
    ])]);
    $payments = new PaymentsResource(API_KEY, BASE_URL, 'wajub', $http);

    $payment = $payments->create(['amount' => 5000, 'currency' => 'XAF']);

    expect($payment->id)->toBe('pay_123')
        ->and($payment->authorization_token)->toBe('tok_abc')
        ->and($payment->authorization_url)->toBe('https://checkout.test/pay/pay_123')
        ->and($payment['id'])->toBe('pay_123')
        ->and($payment['authorization_token'])->toBe('tok_abc')
        ->and($payment['authorization_url'])->toBe('https://checkout.test/pay/pay_123');
});

it('aliases initialize to create', function (): void {
    $http = mockHttpClient([jsonResponse([
        'transaction' => ['id' => 'pay_456'],
        'authorization_url' => 'https://checkout.test/pay/pay_456',
    ])]);
    $payments = new PaymentsResource(API_KEY, BASE_URL, 'wajub', $http);

    $payment = $payments->initialize(['amount' => 1000, 'currency' => 'XAF']);

    expect($payment->id)->toBe('pay_456')
        ->and($payment['id'])->toBe('pay_456');
});

it('retrieves a payment', function (): void {
    $http = mockHttpClient([jsonResponse(['transaction' => ['id' => 'pay_789', 'status' => 'succeeded']])]);
    $payments = new PaymentsResource(API_KEY, BASE_URL, 'wajub', $http);

    $payment = $payments->retrieve('pay_789');

    expect($payment->status)->toBe('succeeded')
        ->and($payment['status'])->toBe('succeeded');
});

it('paginates payment lists', function (): void {
    $http = mockHttpClient([
        jsonResponse(['transactions' => [['id' => 'pay_1']], 'meta' => ['current_page' => 1, 'last_page' => 2]]),
        jsonResponse(['transactions' => [['id' => 'pay_2']], 'meta' => ['current_page' => 2, 'last_page' => 2]]),
    ]);
    $payments = new PaymentsResource(API_KEY, BASE_URL, 'wajub', $http);

    $page1 = $payments->list(['per_page' => 1]);
    expect($page1->data[0]['id'])->toBe('pay_1')
        ->and($page1->hasMore)->toBeTrue();

    $page2 = $page1->getNextPage();
    expect($page2->data[0]['id'])->toBe('pay_2')
        ->and($page2->hasMore)->toBeFalse();
});

it('creates and retrieves refunds, and has no update or delete', function (): void {
    [$http, $mock] = mockClientWithCapture([
        jsonResponse(['refund' => ['id' => 'ref_new', 'status' => 'pending']], 201),
        jsonResponse(['refund' => ['id' => 'ref_new', 'status' => 'succeeded']]),
    ]);
    $refunds = new RefundsResource(API_KEY, BASE_URL, 'wajub', $http);

    $created = $refunds->create(['payment' => 'trx_1', 'amount' => 500, 'reason' => 'requested_by_customer']);

    expect($created)->toBeInstanceOf(Refund::class)
        ->and($created->id)->toBe('ref_new')
        ->and(json_decode((string) $mock->getLastRequest()?->getBody(), true))
        ->toBe(['payment' => 'trx_1', 'amount' => 500, 'reason' => 'requested_by_customer'])
        ->and($refunds->retrieve('ref_new')['status'])->toBe('succeeded')
        ->and(method_exists($refunds, 'update'))->toBeFalse()
        ->and(method_exists($refunds, 'delete'))->toBeFalse();
});

it('exposes create, retrieve and list only on transfers', function (): void {
    $transfers = new TransfersResource(API_KEY, BASE_URL, 'wajub', mockHttpClient([]));

    expect(method_exists($transfers, 'create'))->toBeTrue()
        ->and(method_exists($transfers, 'retrieve'))->toBeTrue()
        ->and(method_exists($transfers, 'list'))->toBeTrue()
        ->and(method_exists($transfers, 'update'))->toBeFalse()
        ->and(method_exists($transfers, 'delete'))->toBeFalse();
});

it('keeps the next-step keys returned by process', function (): void {
    $http = mockHttpClient([jsonResponse([
        'transaction' => ['id' => 'trx_1', 'status' => 'processing'],
        'action' => ['type' => 'redirect', 'url' => 'https://bank.test/3ds'],
        'confirm_url' => 'https://bank.test/3ds',
        'simulator_url' => 'https://api.test/sandbox/payments/trx_1/simulate',
    ])]);
    $payments = new PaymentsResource(API_KEY, BASE_URL, 'wajub', $http);

    $payment = $payments->process('trx_1', ['channel' => 'card']);

    expect($payment->status)->toBe('processing')
        ->and($payment->action['type'])->toBe('redirect')
        ->and($payment->confirm_url)->toBe('https://bank.test/3ds')
        ->and($payment->simulator_url)->toContain('/simulate');
});

it('follows cursor pagination when the api answers with a cursor', function (): void {
    [$http, $mock] = mockClientWithCapture([
        jsonResponse(['items' => [['id' => 'trx_1']], 'meta' => ['per_page' => 1, 'next_cursor' => 'abc', 'prev_cursor' => null, 'has_more' => true]]),
        jsonResponse(['items' => [['id' => 'trx_2']], 'meta' => ['per_page' => 1, 'next_cursor' => null, 'prev_cursor' => 'xyz', 'has_more' => false]]),
    ]);
    $payments = new PaymentsResource(API_KEY, BASE_URL, 'wajub', $http);

    $page1 = $payments->list(['cursor' => 'start', 'per_page' => 1, 'status' => 'succeeded']);
    expect($page1->data[0]['id'])->toBe('trx_1')
        ->and($page1->hasMore)->toBeTrue();

    $page2 = $page1->getNextPage();
    $query = $mock->getLastRequest()?->getUri()->getQuery();

    expect($page2->data[0]['id'])->toBe('trx_2')
        ->and($page2->hasMore)->toBeFalse()
        ->and($query)->toContain('cursor=abc')
        ->toContain('status=succeeded')
        ->not->toContain('&page=');
});

it('passes the currency filter to balance', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['balance' => ['total' => 100, 'currency' => 'XAF']])]);
    $balance = new BalanceResource(API_KEY, BASE_URL, 'wajub', $http);

    expect($balance->retrieve(['currency' => 'XAF'])['currency'])->toBe('XAF')
        ->and($mock->getLastRequest()?->getUri()->getQuery())->toBe('currency=XAF');
});

it('sends merchant_response when accepting or closing a dispute', function (): void {
    [$http, $mock] = mockClientWithCapture([
        jsonResponse(['dispute' => ['id' => 'dsp_1', 'status' => 'resolved']]),
        jsonResponse(['dispute' => ['id' => 'dsp_1', 'status' => 'resolved']]),
    ]);
    $disputes = new DisputesResource(API_KEY, BASE_URL, 'wajub', $http);

    $disputes->accept('dsp_1', ['merchant_response' => 'ok']);
    expect(json_decode((string) $mock->getLastRequest()?->getBody(), true))->toBe(['merchant_response' => 'ok']);

    $disputes->close('dsp_1');
    expect((string) $mock->getLastRequest()?->getBody())->toBe('');
});

it('unwraps tax reports, tax codes, listen and shield responses under their api keys', function (): void {
    $http = mockHttpClient([
        jsonResponse(['report' => ['period' => '30d', 'total_tax' => 12.5]]),
        jsonResponse(['tax_code' => ['code' => 'txcd_1', 'name' => 'General']]),
        jsonResponse(['realtime' => ['key' => 'k', 'channel' => 'private-team.1.sandbox']]),
        jsonResponse(['data' => ['auth' => 'sig']]),
        jsonResponse(['blocklist' => [['id' => 'bl_1', 'type' => 'email']]]),
    ]);

    $tax = new TaxResource(API_KEY, BASE_URL, 'wajub', $http);
    expect($tax->reports(['period' => '30d'])['total_tax'])->toBe(12.5)
        ->and($tax->retrieveCode('txcd_1')['name'])->toBe('General');

    $listen = new ListenResource(API_KEY, BASE_URL, 'wajub', $http);
    expect($listen->config()['channel'])->toBe('private-team.1.sandbox')
        ->and($listen->auth(['socket_id' => '1', 'channel_name' => 'c'])['auth'])->toBe('sig');

    $shield = new ShieldResource(API_KEY, BASE_URL, 'wajub', $http);
    expect($shield->addToBlocklist(['type' => 'email', 'value' => 'a@b.c'])['data'][0]['id'])->toBe('bl_1');
});
