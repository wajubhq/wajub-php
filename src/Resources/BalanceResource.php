<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;

final class BalanceResource extends BaseClient
{
    /** @return array<string, mixed> */
    public function retrieve(): array
    {
        return HttpUtils::pickResource($this->get('/balance'), 'balance', 'data');
    }
}
