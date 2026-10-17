<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

/**
 * @deprecated 2.1.0 The API answers both methods 410 Gone. To receive webhooks
 *             on your machine, forward them with the Wajub CLI's `wajub listen`.
 *             This class goes in the next major version.
 */
final class ListenResource extends BaseClient
{
    /**
     * @deprecated 2.1.0 The API answers it 410 Gone. To receive webhooks on your
     *             machine, forward them with the Wajub CLI's `wajub listen`.
     *
     * @return array<string, mixed>
     */
    public function config(): array
    {
        $this->deprecated('config');

        return HttpUtils::pickResource($this->get('/listen/config'), 'realtime');
    }

    /**
     * @deprecated 2.1.0 The API answers it 410 Gone. To receive webhooks on your
     *             machine, forward them with the Wajub CLI's `wajub listen`.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function auth(array $params, ?RequestOptions $options = null): array
    {
        $this->deprecated('auth');

        return HttpUtils::pickResource($this->post('/listen/auth', $params, $options), 'data');
    }

    private function deprecated(string $method): void
    {
        trigger_error("Wajub: listen->{$method}() is deprecated since 2.1.0 and the API answers it 410 Gone. To receive webhooks on your machine, forward them with the Wajub CLI's `wajub listen`.", E_USER_DEPRECATED);
    }
}
