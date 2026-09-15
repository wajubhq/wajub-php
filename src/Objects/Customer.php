<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A customer from `POST /customers` and `GET /customers/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $type `individual` or `business`
 * @property-read string|null $name
 * @property-read string|null $email
 * @property-read string|null $phone
 * @property-read string|null $phone_country
 * @property-read string|null $description
 * @property-read string|null $notes
 * @property-read string|null $currency
 * @property-read string|null $status `active`, `inactive` or `blocked`
 * @property-read bool|null $blocked
 * @property-read string|null $block_reason
 * @property-read string|null $blocked_at
 * @property-read string|null $customer_group
 * @property-read array<int, string>|null $tags
 * @property-read array<int, string>|null $preferred_currencies
 * @property-read array<int, string>|null $preferred_locales
 * @property-read array<string, mixed>|null $metadata
 * @property-read array<string, mixed>|null $billing
 * @property-read array<string, mixed>|null $address
 * @property-read string|null $first_name Individuals only.
 * @property-read string|null $last_name Individuals only.
 * @property-read string|null $date_of_birth Individuals only, `Y-m-d`.
 * @property-read string|null $gender Individuals only.
 * @property-read string|null $business_name Businesses only.
 * @property-read string|null $business_type Businesses only.
 * @property-read string|null $business_registration_number Businesses only.
 * @property-read string|null $tax_id Businesses only.
 * @property-read string|null $tax_exempt Businesses only.
 * @property-read string|null $legal_representative_name Businesses only.
 * @property-read string|null $legal_representative_title Businesses only.
 * @property-read bool|null $sandbox
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Customer extends ApiObject
{
}
