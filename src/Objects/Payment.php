<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * @property-read string|null $id
 * @property-read string|null $status
 * @property-read int|null $amount
 * @property-read string|null $currency
 * @property-read string|null $authorization_url
 * @property-read string|null $authorization_token
 */
final class Payment extends ApiObject
{
}
