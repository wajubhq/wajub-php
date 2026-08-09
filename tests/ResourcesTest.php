<?php

declare(strict_types=1);

namespace Wajub\Tests;

use GuzzleHttp\Psr7\Response;
use Wajub\Resources\PaymentsResource;
use Wajub\Resources\RefundsResource;

final class ResourcesTest extends TestCase
{
    public function test_payments_create_unwraps_transaction_and_urls(): void
    {
        $http = $this->mockHttpClient([
            new Response(200, [], json_encode([
                'transaction' => ['id' => 'pay_123', 'status' => 'pending'],
                'authorization_token' => 'tok_abc',
                'authorization_url' => 'https://checkout.test/pay/pay_123',
            ])),
        ]);
        $payments = new PaymentsResource(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $payment = $payments->create([
            'amount' => 5000,
            'currency' => 'XAF',
        ]);

        $this->assertSame('pay_123', $payment->id);
        $this->assertSame('tok_abc', $payment->authorization_token);
        $this->assertSame('https://checkout.test/pay/pay_123', $payment->authorization_url);
        $this->assertSame('pay_123', $payment['id']);
        $this->assertSame('tok_abc', $payment['authorization_token']);
        $this->assertSame('https://checkout.test/pay/pay_123', $payment['authorization_url']);
    }

    public function test_payments_initialize_is_alias_for_create(): void
    {
        $http = $this->mockHttpClient([
            new Response(200, [], json_encode([
                'transaction' => ['id' => 'pay_456'],
                'authorization_url' => 'https://checkout.test/pay/pay_456',
            ])),
        ]);
        $payments = new PaymentsResource(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $payment = $payments->initialize(['amount' => 1000, 'currency' => 'XAF']);

        $this->assertSame('pay_456', $payment->id);
        $this->assertSame('pay_456', $payment['id']);
    }

    public function test_payments_retrieve(): void
    {
        $http = $this->mockHttpClient([
            new Response(200, [], json_encode([
                'transaction' => ['id' => 'pay_789', 'status' => 'succeeded'],
            ])),
        ]);
        $payments = new PaymentsResource(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $payment = $payments->retrieve('pay_789');

        $this->assertSame('succeeded', $payment->status);
        $this->assertSame('succeeded', $payment['status']);
    }

    public function test_payments_list_pagination(): void
    {
        $http = $this->mockHttpClient([
            new Response(200, [], json_encode([
                'transactions' => [['id' => 'pay_1']],
                'meta' => ['current_page' => 1, 'last_page' => 2],
            ])),
            new Response(200, [], json_encode([
                'transactions' => [['id' => 'pay_2']],
                'meta' => ['current_page' => 2, 'last_page' => 2],
            ])),
        ]);
        $payments = new PaymentsResource(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $page1 = $payments->list(['per_page' => 1]);
        $this->assertSame('pay_1', $page1->data[0]['id']);
        $this->assertTrue($page1->hasMore);

        $page2 = $page1->getNextPage();
        $this->assertSame('pay_2', $page2->data[0]['id']);
        $this->assertFalse($page2->hasMore);
    }

    public function test_refunds_crud(): void
    {
        $http = $this->mockHttpClient([
            new Response(200, [], json_encode(['refund' => ['id' => 'ref_new']])),
            new Response(200, [], json_encode(['refund' => ['id' => 'ref_new', 'status' => 'pending']])),
            new Response(200, [], json_encode([])),
        ]);
        $refunds = new RefundsResource(self::API_KEY, self::BASE_URL, 'wajub', $http);

        $created = $refunds->create(['payment_id' => 'pay_1', 'amount' => 500]);
        $this->assertSame('ref_new', $created['id']);

        $retrieved = $refunds->retrieve('ref_new');
        $this->assertSame('pending', $retrieved['status']);

        $refunds->delete('ref_new');
        $this->assertTrue(true);
    }
}
