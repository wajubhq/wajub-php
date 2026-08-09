<?php

declare(strict_types=1);

namespace Wajub\Tests;

use Wajub\Objects\Payment;

final class ObjectsTest extends TestCase
{
    public function test_payment_object_supports_property_and_array_access(): void
    {
        $payment = Payment::from([
            'id' => 'pay_123',
            'status' => 'pending',
            'authorization_url' => 'https://checkout.test/pay/pay_123',
        ]);

        $this->assertSame('pay_123', $payment->id);
        $this->assertSame('pay_123', $payment['id']);
        $this->assertSame('pending', $payment->status);
    }
}
