<?php

declare(strict_types=1);

use Wajub\Exception\WebhookSignatureVerificationError;
use Wajub\Webhooks;

const WEBHOOK_SECRET = 'whsec_test_abc123';

it('parses a correctly signed payload', function (): void {
    $payload = '{"event":"payment.succeeded","data":{"id":"trx_1"}}';
    $timestamp = time();

    $event = new Webhooks(WEBHOOK_SECRET)->constructEvent(
        $payload,
        signWebhook(WEBHOOK_SECRET, $payload, $timestamp),
        (string) $timestamp,
    );

    expect($event['event'])->toBe('payment.succeeded')
        ->and($event['data']['id'])->toBe('trx_1');
});

it('rejects a signature that is not v1 formatted', function (): void {
    new Webhooks(WEBHOOK_SECRET)->constructEvent('{}', 'not-v1-format', (string) time());
})->throws(WebhookSignatureVerificationError::class);

it('rejects a wrong signature', function (): void {
    new Webhooks(WEBHOOK_SECRET)->constructEvent('{"event":"payment.succeeded"}', 'v1=deadbeef', (string) time());
})->throws(WebhookSignatureVerificationError::class);

it('rejects a timestamp outside the tolerance window', function (): void {
    $payload = '{"event":"payment.succeeded"}';
    $oldTimestamp = time() - 600;

    new Webhooks(WEBHOOK_SECRET)->constructEvent(
        $payload,
        signWebhook(WEBHOOK_SECRET, $payload, $oldTimestamp),
        (string) $oldTimestamp,
        300,
    );
})->throws(WebhookSignatureVerificationError::class, 'Timestamp outside tolerance zone');

it('requires a webhook secret', function (): void {
    new Webhooks('')->constructEvent('{}', 'v1=abc', (string) time());
})->throws(InvalidArgumentException::class);
