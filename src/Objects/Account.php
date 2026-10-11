<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A connected account from `POST /accounts` and `GET /accounts/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $reference
 * @property-read string|null $account_type
 * @property-read string|null $status
 * @property-read string|null $payment_status
 * @property-read string|null $callback
 * @property-read string|null $authorization_url Present when a token was just issued.
 * @property-read array<int|string, mixed>|null $capabilities
 * @property-read array<string, mixed>|null $profile
 * @property-read array<string, mixed>|null $pricing
 * @property-read bool|null $sandbox
 * @property-read string|null $expires_at
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Account extends ApiObject
{
}
