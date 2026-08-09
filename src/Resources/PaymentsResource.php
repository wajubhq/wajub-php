<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\Objects\Payment;
use Wajub\RequestOptions;

final class PaymentsResource extends BaseClient
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function create(array $params, ?RequestOptions $options = null): Payment
    {
        $idempotencyKey = $params['idempotencyKey'] ?? null;
        unset($params['idempotencyKey']);
        $opts = $options ?? new RequestOptions;
        if ($idempotencyKey !== null) {
            $opts = new RequestOptions(
                idempotencyKey: $idempotencyKey,
                headers: $opts->headers,
                sync: $opts->sync,
            );
        }

        return $this->toPaymentObject($this->post('/payments', $params, $opts));
    }

    public function initialize(array $params, ?RequestOptions $options = null): Payment
    {
        return $this->create($params, $options);
    }

    public function retrieve(string $id): Payment
    {
        return Payment::from(
            HttpUtils::pickResource($this->get('/payments/'.rawurlencode($id)), 'transaction'),
        );
    }

    /**
     * @param  array<string, mixed>|null  $params
     */
    public function list(?array $params = null): PagedResult
    {
        return Pagination::createPagedList($this, '/payments', 'transactions', $params);
    }

    public function cancel(string $id, ?RequestOptions $options = null): Payment
    {
        return Payment::from(
            HttpUtils::pickResource(
                $this->del('/payments/'.rawurlencode($id), $options),
                'transaction',
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function process(string $id, array $params, ?RequestOptions $options = null): Payment
    {
        return Payment::from(
            HttpUtils::pickResource(
                $this->post('/payments/'.rawurlencode($id), $params, $options),
                'transaction',
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function processSplit(string $id, array $params, ?RequestOptions $options = null): Payment
    {
        return Payment::from(
            HttpUtils::pickResource(
                $this->post('/payments/'.rawurlencode($id).'/splits', $params, $options),
                'transaction',
            ),
        );
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function listRefunds(string $id, ?array $params = null): array
    {
        return HttpUtils::pickList(
            $this->get('/payments/'.rawurlencode($id).'/refunds', $params),
            'refunds',
        );
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function toPaymentObject(array $body): Payment
    {
        $transaction = HttpUtils::pickResource($body, 'transaction');

        return Payment::from(array_merge($transaction, [
            'authorization_token' => (string) ($body['authorization_token'] ?? $transaction['authorization_token'] ?? $transaction['id'] ?? ''),
            'authorization_url' => (string) ($body['authorization_url'] ?? $transaction['authorization_url'] ?? ''),
        ]));
    }
}
