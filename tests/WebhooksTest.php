<?php

declare(strict_types=1);

namespace Wajub\Tests;

use Wajub\Crypto;
use Wajub\Exception\WebhookSignatureVerificationError;
use Wajub\Webhooks;

final class WebhooksTest extends TestCase
{
    private const SECRET = 'whsec_test_abc123';

    private function sign(string $payload, int $timestamp): string
    {
        return 'v1='.Crypto::hmacSha256(self::SECRET, "{$timestamp}.{$payload}");
    }

    public function test_construct_event_parses_valid_payload(): void
    {
        $payload = '{"event":"payment.succeeded","data":{"id":"trx_1"}}';
        $timestamp = time();
        $webhooks = new Webhooks(self::SECRET);

        $event = $webhooks->constructEvent($payload, $this->sign($payload, $timestamp), (string) $timestamp);

        $this->assertSame('payment.succeeded', $event['event']);
        $this->assertSame('trx_1', $event['data']['id']);
    }

    public function test_construct_event_rejects_invalid_signature_format(): void
    {
        $webhooks = new Webhooks(self::SECRET);

        $this->expectException(WebhookSignatureVerificationError::class);
        $webhooks->constructEvent('{}', 'not-v1-format', (string) time());
    }

    public function test_construct_event_rejects_wrong_signature(): void
    {
        $payload = '{"event":"payment.succeeded"}';
        $webhooks = new Webhooks(self::SECRET);

        $this->expectException(WebhookSignatureVerificationError::class);
        $webhooks->constructEvent($payload, 'v1=deadbeef', (string) time());
    }

    public function test_construct_event_rejects_timestamp_outside_tolerance(): void
    {
        $payload = '{"event":"payment.succeeded"}';
        $oldTimestamp = time() - 600;
        $webhooks = new Webhooks(self::SECRET);

        $this->expectException(WebhookSignatureVerificationError::class);
        $webhooks->constructEvent(
            $payload,
            $this->sign($payload, $oldTimestamp),
            (string) $oldTimestamp,
            300,
        );
    }

    public function test_construct_event_requires_webhook_secret(): void
    {
        $webhooks = new Webhooks('');

        $this->expectException(\InvalidArgumentException::class);
        $webhooks->constructEvent('{}', 'v1=abc', (string) time());
    }
}
