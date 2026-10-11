<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * A webhook endpoint from `POST /webhooks` and `GET /webhooks/{uid}`.
 *
 * @property-read string|null $id
 * @property-read string|null $url
 * @property-read string|null $description
 * @property-read array<int, string>|null $events
 * @property-read string|null $status
 * @property-read bool|null $active
 * @property-read string|null $secret Only when the endpoint is created or its secret rotated.
 * @property-read bool|null $sandbox
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 */
final class WebhookEndpoint extends ApiObject
{
}
