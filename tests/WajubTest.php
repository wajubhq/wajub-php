<?php

declare(strict_types=1);

namespace Wajub\Tests;

use Wajub\Crypto;
use Wajub\Exception\WebhookSignatureVerificationError;
use Wajub\Wajub;

/**
 * Integration-level tests for the Wajub SDK entry point.
 *
 * These tests confirm that:
 *  - The constructor enforces a required api_key.
 *  - All resource getters are accessible.
 *  - The bundled $wajub->webhooks accessor correctly delegates to Webhooks,
 *    providing a single-class integration smoke-test for signature verification.
 */
final class WajubTest extends TestCase
{
    // ─── Constructor ──────────────────────────────────────────────────────────

    public function test_requires_api_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('api_key is required');

        new Wajub([]);
    }

    public function test_requires_non_empty_api_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('api_key is required');

        new Wajub(['api_key' => '']);
    }

    public function test_accepts_public_and_restricted_keys(): void
    {
        $public     = new Wajub(['api_key' => 'pk_test.public']);
        $restricted = new Wajub(['api_key' => 'rk_test.restricted']);

        $this->assertInstanceOf(Wajub::class, $public);
        $this->assertInstanceOf(Wajub::class, $restricted);
    }

    // ─── Resource getters ─────────────────────────────────────────────────────

    public function test_exposes_all_resource_getters(): void
    {
        $wajub = new Wajub(['api_key' => self::API_KEY]);

        $this->assertNotNull($wajub->global);
        $this->assertNotNull($wajub->payments);
        $this->assertNotNull($wajub->customers);
        $this->assertNotNull($wajub->refunds);
        $this->assertNotNull($wajub->transfers);
        $this->assertNotNull($wajub->beneficiaries);
        $this->assertNotNull($wajub->links);
        $this->assertNotNull($wajub->balance);
        $this->assertNotNull($wajub->events);
        $this->assertNotNull($wajub->accounts);
        $this->assertNotNull($wajub->webhookEndpoints);
        $this->assertNotNull($wajub->invoices);
        $this->assertNotNull($wajub->disputes);
        $this->assertNotNull($wajub->identity);
        $this->assertNotNull($wajub->tax);
        $this->assertNotNull($wajub->shield);
        $this->assertNotNull($wajub->listen);
        $this->assertNotNull($wajub->webhooks);
    }

    // ─── Webhooks via $wajub->webhooks ────────────────────────────────────────

    private function sign(string $secret, string $payload, int $timestamp): string
    {
        return 'v1=' . Crypto::hmacSha256($secret, "{$timestamp}.{$payload}");
    }

    public function test_webhooks_constructs_valid_event_via_wajub_entry_point(): void
    {
        $secret  = 'whsec_test_sdk_entry';
        $payload = '{"type":"payment.succeeded","data":{"id":"trx_abc"}}';
        $ts      = time();

        $wajub = new Wajub([
            'api_key'        => self::API_KEY,
            'webhook_secret' => $secret,
        ]);

        $event = $wajub->webhooks->constructEvent(
            $payload,
            $this->sign($secret, $payload, $ts),
            (string) $ts,
        );

        $this->assertSame('payment.succeeded', $event['type']);
        $this->assertSame('trx_abc', $event['data']['id']);
    }

    public function test_webhooks_rejects_invalid_signature(): void
    {
        $wajub = new Wajub([
            'api_key'        => self::API_KEY,
            'webhook_secret' => 'whsec_test_sdk_entry',
        ]);

        $this->expectException(WebhookSignatureVerificationError::class);

        $wajub->webhooks->constructEvent(
            '{"type":"payment.succeeded"}',
            'not-v1-format',
            (string) time(),
        );
    }

    public function test_webhooks_rejects_wrong_signature(): void
    {
        $wajub = new Wajub([
            'api_key'        => self::API_KEY,
            'webhook_secret' => 'whsec_test_sdk_entry',
        ]);

        $this->expectException(WebhookSignatureVerificationError::class);

        $wajub->webhooks->constructEvent(
            '{"type":"payment.succeeded"}',
            'v1=deadbeef00000000000000000000000000000000000000000000000000000000',
            (string) time(),
        );
    }

    public function test_webhooks_rejects_expired_timestamp(): void
    {
        $secret  = 'whsec_test_sdk_entry';
        $payload = '{"type":"payment.succeeded"}';

        // Timestamp 10 minutes in the past — outside the default 300 s window.
        $oldTs = time() - 600;

        $wajub = new Wajub([
            'api_key'        => self::API_KEY,
            'webhook_secret' => $secret,
        ]);

        $this->expectException(WebhookSignatureVerificationError::class);
        $this->expectExceptionMessage('Timestamp outside tolerance zone');

        $wajub->webhooks->constructEvent(
            $payload,
            $this->sign($secret, $payload, $oldTs),
            (string) $oldTs,
            300,
        );
    }

    public function test_webhooks_requires_webhook_secret_to_be_configured(): void
    {
        // Creating without a webhook_secret (and WAJUB_WEBHOOK_SECRET not set).
        $wajub = new Wajub(['api_key' => self::API_KEY]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('webhookSecret is required');

        $wajub->webhooks->constructEvent('{}', 'v1=abc', (string) time());
    }
}
