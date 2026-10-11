<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A beneficiary from `POST /beneficiaries` and `GET /beneficiaries/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $name
 * @property-read string|null $email
 * @property-read string|null $phone
 * @property-read string|null $country_code
 * @property-read string|null $note
 * @property-read string|null $type
 * @property-read bool|null $is_active
 * @property-read bool|null $verified
 * @property-read string|null $verification
 * @property-read array<string, mixed>|null $payment_method
 * @property-read array<string, mixed>|null $metadata
 * @property-read bool|null $sandbox
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Beneficiary extends ApiObject
{
}
