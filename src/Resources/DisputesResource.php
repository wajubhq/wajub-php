<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class DisputesResource extends BaseClient
{
    /**
     * @param  array<string, mixed>|null  $params
     */
    public function list(?array $params = null): PagedResult
    {
        return Pagination::createPagedList($this, '/disputes', 'disputes', $params);
    }

    /** @return array<string, mixed> */
    public function retrieve(string $id): array
    {
        return HttpUtils::pickResource($this->get('/disputes/'.rawurlencode($id)), 'dispute');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function submitEvidence(string $id, array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/disputes/'.rawurlencode($id).'/submit-evidence', $params, $options),
            'dispute',
        );
    }

    /**
     * `POST /disputes/{uid}/accept`. Accepts `['merchant_response' => '...']`.
     *
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function accept(string $id, ?array $params = null, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/disputes/'.rawurlencode($id).'/accept', $params, $options),
            'dispute',
        );
    }

    /**
     * `POST /disputes/{uid}/close`. Accepts `['merchant_response' => '...']`.
     *
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function close(string $id, ?array $params = null, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/disputes/'.rawurlencode($id).'/close', $params, $options),
            'dispute',
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function sendMessage(string $id, array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/disputes/'.rawurlencode($id).'/messages', $params, $options),
            'dispute',
        );
    }
}
