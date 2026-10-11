<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A payment link from `POST /links` and `GET /links/{uid}`.
 *
 * Only the fields every link carries are listed here; the ones that belong to
 * one link type (events, fundraising, products) are read the same way.
 *
 * @property-read string|null $id
 * @property-read string|null $type
 * @property-read string|null $product_type
 * @property-read string|null $title
 * @property-read string|null $description
 * @property-read string|null $external_reference
 * @property-read string|null $url
 * @property-read string|null $qr_url
 * @property-read float|null $price
 * @property-read float|null $min_amount
 * @property-read float|null $max_amount
 * @property-read string|null $currency
 * @property-read string|null $callback_url
 * @property-read string|null $expiry_date
 * @property-read int|null $sales_limit
 * @property-read bool|null $is_public
 * @property-read bool|null $is_archived
 * @property-read bool|null $allow_custom_amount
 * @property-read array<int, string>|null $tags
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class Link extends ApiObject
{
}
