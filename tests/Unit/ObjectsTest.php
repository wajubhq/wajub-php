<?php

declare(strict_types=1);

use Wajub\Objects\Payment;

it('supports property and array access on payment objects', function (): void {
    $payment = Payment::from([
        'id' => 'pay_123',
        'status' => 'pending',
        'authorization_url' => 'https://checkout.test/pay/pay_123',
    ]);

    expect($payment->id)->toBe('pay_123')
        ->and($payment['id'])->toBe('pay_123')
        ->and($payment->status)->toBe('pending');
});
