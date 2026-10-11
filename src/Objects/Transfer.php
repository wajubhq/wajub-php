<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A transfer from `POST /transfers` and `GET /transfers/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $reference
 * @property-read string|null $provider_reference
 * @property-read float|null $amount
 * @property-read string|null $currency
 * @property-read string|null $description
 * @property-read string|null $reason
 * @property-read string|null $status
 * @property-read bool|null $complete
 * @property-read string|null $source
 * @property-read string|null $failure_reason Failed transfers only.
 * @property-read string|null $failure_message Failed transfers only.
 * @property-read array<string, mixed>|null $beneficiary
 * @property-read array<string, mixed>|null $payment_method
 * @property-read array<string, mixed>|null $provider
 * @property-read array<string, mixed>|null $metadata
 * @property-read bool|null $sandbox
 * @property-read string|null $succeeded_at
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Transfer extends ApiObject
{
}
