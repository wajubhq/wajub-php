<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;

final class BalanceResource extends BaseClient
{
    /**
     * `GET /balance`. Pass `['currency' => 'XAF']` to convert the totals.
     *
     * @param  array<string, mixed>|null  $params
     * @return array<string, mixed>
     */
    public function retrieve(?array $params = null): array
    {
        return HttpUtils::pickResource($this->get('/balance', $params), 'balance');
    }
}
