#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Wajub\Wajub;

$wajub = new Wajub(['api_key' => getenv('WAJUB_API_KEY') ?: 'sk_test_your_key']);

$payment = $wajub->payments->create([
    'amount' => 15000,
    'currency' => 'XAF',
    'email' => 'buyer@example.com',
    'callback' => 'https://shop.example.com/complete',
]);

echo "Payment {$payment->id} — redirect buyer to:\n";
echo $payment->authorization_url."\n";
