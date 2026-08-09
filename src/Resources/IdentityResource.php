<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class IdentityResource extends BaseClient
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function resolve(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource($this->post('/identity/resolve', $params, $options), 'identity');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function validate(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource($this->post('/identity/validate', $params, $options), 'identity');
    }
}
