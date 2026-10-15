<?php

declare(strict_types=1);

use Wajub\Objects\Account;
use Wajub\Objects\ApiObject;
use Wajub\Objects\Beneficiary;
use Wajub\Objects\Invoice;
use Wajub\Objects\Link;
use Wajub\Objects\Refund;
use Wajub\Objects\Transfer;
use Wajub\Objects\WebhookEndpoint;
use Wajub\Resources\AccountsResource;
use Wajub\Resources\BalanceResource;
use Wajub\Resources\BeneficiariesResource;
use Wajub\Resources\CrudResource;
use Wajub\Resources\DisputesResource;
use Wajub\Resources\InvoicesResource;
use Wajub\Resources\LinksResource;
use Wajub\Resources\ListenResource;
use Wajub\Resources\PaymentsResource;
use Wajub\Resources\RefundsResource;
use Wajub\Resources\Resource;
use Wajub\Resources\ShieldResource;
use Wajub\Resources\TaxResource;
use Wajub\Resources\TransfersResource;
use Wajub\Resources\WebhookEndpointsResource;
use Wajub\Wajub;

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

/**
 * 2.0.0 sent the request, then failed building the answer: these six resources
 * named no object class, so the result fell back to the abstract ApiObject and
 * PHP threw "Cannot instantiate abstract class". The transfer was created, the
 * caller got an Error instead of it, and a retry without an idempotency key
 * sent the money twice.
 */
it('returns an object from create and retrieve on every resource', function (string $resource, string $key, string $object): void {
    $http = mockHttpClient([
        jsonResponse([$key => ['id' => 'obj_1', 'status' => 'pending']], 201),
        jsonResponse([$key => ['id' => 'obj_1', 'status' => 'succeeded']]),
    ]);
    $client = new $resource(API_KEY, BASE_URL, 'wajub', $http);

    $created = $client->create(['name' => 'x']);
    $retrieved = $client->retrieve('obj_1');

    expect($created)->toBeInstanceOf($object)
        ->and($created->id)->toBe('obj_1')
        ->and($created['status'])->toBe('pending')
        ->and($retrieved)->toBeInstanceOf($object)
        ->and($retrieved->status)->toBe('succeeded');
})->with([
    'transfers' => [TransfersResource::class, 'transfer', Transfer::class],
    'beneficiaries' => [BeneficiariesResource::class, 'beneficiary', Beneficiary::class],
    'links' => [LinksResource::class, 'link', Link::class],
    'invoices' => [InvoicesResource::class, 'invoice', Invoice::class],
    'accounts' => [AccountsResource::class, 'account', Account::class],
    'webhook endpoints' => [WebhookEndpointsResource::class, 'endpoint', WebhookEndpoint::class],
]);

it('returns an object from update on every resource that has one', function (string $resource, string $key, string $object): void {
    $http = mockHttpClient([jsonResponse([$key => ['id' => 'obj_1', 'name' => 'renamed']])]);

    $updated = new $resource(API_KEY, BASE_URL, 'wajub', $http)->update('obj_1', ['name' => 'renamed']);

    expect($updated)->toBeInstanceOf($object)
        ->and($updated->name)->toBe('renamed');
})->with([
    'beneficiaries' => [BeneficiariesResource::class, 'beneficiary', Beneficiary::class],
    'links' => [LinksResource::class, 'link', Link::class],
    'invoices' => [InvoicesResource::class, 'invoice', Invoice::class],
    'accounts' => [AccountsResource::class, 'account', Account::class],
    'webhook endpoints' => [WebhookEndpointsResource::class, 'endpoint', WebhookEndpoint::class],
]);

it('gives no resource of the client an object class it cannot build', function (): void {
    $wajub = new Wajub(['api_key' => API_KEY]);
    $objectClass = new ReflectionProperty(Resource::class, 'objectClass');

    foreach (new ReflectionClass($wajub)->getProperties() as $property) {
        $resource = $property->getValue($wajub);

        if (! $resource instanceof Resource) {
            continue;
        }

        $class = new ReflectionClass($objectClass->getValue($resource));

        expect($class->isInstantiable())->toBeTrue("{$property->getName()} builds {$class->getName()}")
            ->and($class->isSubclassOf(ApiObject::class))->toBeTrue();
    }
});

it('refuses an object class it cannot build before any request is sent', function (): void {
    [$http, $mock] = mockClientWithCapture([jsonResponse(['thing' => ['id' => 'obj_1']], 201)]);

    expect(fn (): CrudResource => new CrudResource(API_KEY, BASE_URL, 'things', 'thing', 'things', 'wajub', $http))
        ->toThrow(InvalidArgumentException::class, 'cannot be instantiated')
        ->and($mock->count())->toBe(1);
});
