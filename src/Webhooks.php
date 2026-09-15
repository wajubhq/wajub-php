<?php

declare(strict_types=1);

namespace Wajub;

use Wajub\Exception\WebhookSignatureVerificationError;

final readonly class Webhooks
{
    private const int DEFAULT_TOLERANCE_SECONDS = 300;

    public function __construct(private string $secret)
    {
    }

    /**
     * Verifies the signature and timestamp of an inbound webhook and returns the decoded event.
     *
     * A tolerance of 0 or less uses the default 300s window; there is no way to disable
     * replay protection.
     *
     * @return array<string, mixed>
     */
    public function constructEvent(
        string $payload,
        string $signature,
        string $timestamp,
        int $tolerance = self::DEFAULT_TOLERANCE_SECONDS,
    ): array {
        if ($this->secret === '') {
            throw new \InvalidArgumentException('Wajub: webhookSecret is required for webhooks.constructEvent()');
        }

        if (! str_starts_with($signature, 'v1=')) {
            throw new WebhookSignatureVerificationError(
                'Invalid webhook signature format. Expected v1={hash}.',
            );
        }

        $hash = substr($signature, 3);
        $expected = Crypto::hmacSha256($this->secret, "{$timestamp}.{$payload}");

        if (! Crypto::timingSafeEqual($expected, $hash)) {
            throw new WebhookSignatureVerificationError('Webhook signature verification failed.');
        }

        $timestampSeconds = (float) $timestamp;
        if (! is_finite($timestampSeconds)) {
            throw new WebhookSignatureVerificationError('Invalid webhook timestamp.');
        }

        if ($tolerance <= 0) {
            $tolerance = self::DEFAULT_TOLERANCE_SECONDS;
        }

        $driftSeconds = abs(time() - $timestampSeconds);
        if ($driftSeconds > $tolerance) {
            throw new WebhookSignatureVerificationError(
                sprintf('Timestamp outside tolerance zone (%ds drift, allowed %ds).', (int) round($driftSeconds), $tolerance),
            );
        }

        $event = json_decode($payload, true);
        if (! is_array($event)) {
            throw new WebhookSignatureVerificationError('Invalid webhook payload JSON.');
        }

        return $event;
    }
}
