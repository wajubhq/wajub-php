<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class ShieldResource extends BaseClient
{
    /** @return array<string, mixed> */
    public function getSettings(): array
    {
        return HttpUtils::pickResource($this->get('/shield/settings'), 'shield', 'settings');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function updateSettings(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource($this->put('/shield/settings', $params, $options), 'shield', 'settings');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function stats(?array $params = null): array
    {
        return HttpUtils::pickResource($this->get('/shield/stats', $params), 'shield', 'stats');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function listBlocklist(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/shield/blocklist', $params), 'blocklist', 'entries');
    }

    /**
     * `POST /shield/blocklist`. The API answers with the updated blocklist, same shape as `listBlocklist()`.
     *
     * @param  array<string, mixed>  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function addToBlocklist(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickList($this->post('/shield/blocklist', $params, $options), 'blocklist');
    }

    public function removeFromBlocklist(string $id, ?RequestOptions $options = null): void
    {
        $this->del('/shield/blocklist/'.rawurlencode($id), $options);
    }
}
