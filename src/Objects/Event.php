<?php

declare(strict_types=1);

namespace Wajub\Objects;

/**
 * An event from `GET /events`. The event name lives in `event`, not `type`.
 *
 * @property-read string|null $id
 * @property-read string|null $event
 * @property-read array<string, mixed>|null $data
 * @property-read bool|null $livemode
 * @property-read int|null $pending_webhooks
 * @property-read string|null $api_version
 * @property-read array{id: string|null, idempotency_key: string|null}|null $request
 * @property-read string|null $created
 */
final class Event extends ApiObject
{
}
