<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class ListenResource extends BaseClient
{
    /** @return array<string, mixed> */
    public function config(): array
    {
        return HttpUtils::pickResource($this->get('/listen/config'), 'realtime');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function auth(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource($this->post('/listen/auth', $params, $options), 'data');
    }
}
