<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A refund from `POST /refunds` and `GET /refunds/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $transaction
 * @property-read string|null $reference
 * @property-read float|null $amount
 * @property-read string|null $currency
 * @property-read string|null $status
 * @property-read string|null $reason
 * @property-read array<string, mixed>|null $metadata
 * @property-read bool|null $sandbox
 * @property-read array<string, mixed>|null $tax
 * @property-read array<int, array<string, mixed>>|null $timeline
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Refund extends ApiObject
{
}
