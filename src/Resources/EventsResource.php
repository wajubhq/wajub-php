<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\Objects\Event;
use Wajub\RequestOptions;

final class EventsResource extends BaseClient
{
    /**
     * @param  array<string, mixed>|null  $params
     */
    public function list(?array $params = null): PagedResult
    {
        return Pagination::createPagedList($this, '/events', 'events', $params);
    }

    public function retrieve(string $id): Event
    {
        return Event::from(
            HttpUtils::pickResource($this->get('/events/'.rawurlencode($id)), 'event'),
        );
    }

    public function resend(string $id, ?RequestOptions $options = null): Event
    {
        return Event::from(
            HttpUtils::pickResource(
                $this->post('/events/'.rawurlencode($id).'/resend', null, $options),
                'event',
            ),
        );
    }
}
