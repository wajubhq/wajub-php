<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * An invoice from `POST /invoices` and `GET /invoices/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $invoice_number
 * @property-read string|null $document_type
 * @property-read string|null $status
 * @property-read string|null $customer_id
 * @property-read string|null $customer_name
 * @property-read string|null $customer_email
 * @property-read string|null $customer_company_name
 * @property-read string|null $customer_phone
 * @property-read string|null $invoice_date `Y-m-d`.
 * @property-read string|null $due_date `Y-m-d`.
 * @property-read string|null $currency
 * @property-read float|null $subtotal
 * @property-read float|null $discount_amount
 * @property-read float|null $tax_amount
 * @property-read float|null $total
 * @property-read float|null $amount_paid
 * @property-read float|null $amount_due
 * @property-read string|null $payment_type
 * @property-read bool|null $is_recurring
 * @property-read string|null $recurring_interval
 * @property-read string|null $notes
 * @property-read string|null $terms
 * @property-read string|null $footer
 * @property-read array<int, array<string, mixed>>|null $items
 * @property-read string|null $sent_at
 * @property-read string|null $paid_at
 * @property-read string|null $cancelled_at
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Invoice extends ApiObject
{
}
