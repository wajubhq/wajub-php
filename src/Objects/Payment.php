<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A payment as returned by `POST /payments`, `GET /payments/{uid}` and the
 * processing endpoints. Amounts are in the currency's major unit.
 *
 * @property-read string|null $id
 * @property-read string|null $reference
 * @property-read float|null $amount
 * @property-read float|null $amount_paid
 * @property-read string|null $currency
 * @property-read string|null $status
 * @property-read string|null $description
 * @property-read array{id?: string, name?: string, email?: string, phone?: string}|null $customer
 * @property-read array{channel?: string, account?: string}|null $payment_method
 * @property-read string|null $channel
 * @property-read string|null $callback
 * @property-read array<string, mixed>|null $theming
 * @property-read bool|null $sandbox
 * @property-read array<int, array<string, mixed>>|null $items
 * @property-read array<string, mixed>|null $metadata
 * @property-read array<string, mixed>|null $shipping_address
 * @property-read array<string, mixed>|null $billing_address
 * @property-read array{amount: float, taxable_amount: float, rate: float, name: string|null, country: string|null, inclusive: bool}|null $tax
 * @property-read string|null $receipt_url
 * @property-read string|null $failure_reason
 * @property-read string|null $credited_at
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 * @property-read string|null $authorization_url Present on `create()`.
 * @property-read string|null $authorization_token Present on `create()`.
 * @property-read array<string, mixed>|null $action Present on `process()` when the payer has a next step.
 * @property-read string|null $confirm_url Present on `process()` for redirect-based channels.
 * @property-read string|null $simulator_url Present on `process()` in sandbox.
 */
final class Payment extends ApiObject
{
}
