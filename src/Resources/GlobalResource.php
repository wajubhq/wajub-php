<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;

final class GlobalResource extends BaseClient
{
    /** @return array<string, mixed> */
    public function ping(): array
    {
        return $this->get('/');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function channels(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/channels', $params), 'channels');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function countries(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/countries', $params), 'countries');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function currencies(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/currencies', $params), 'currencies');
    }
}
