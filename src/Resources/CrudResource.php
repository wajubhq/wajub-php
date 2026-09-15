<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\HttpUtils;
use Wajub\Objects\ApiObject;
use Wajub\RequestOptions;

/**
 * Resource that also supports `PUT /{uid}` and `DELETE /{uid}`.
 */
class CrudResource extends Resource
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function update(string $id, array $params, ?RequestOptions $options = null): ApiObject
    {
        $res = $this->put("/{$this->path}/".rawurlencode($id), $params, $options);

        return $this->toObject(HttpUtils::pickResource($res, $this->singular));
    }

    public function delete(string $id, ?RequestOptions $options = null): void
    {
        $this->del("/{$this->path}/".rawurlencode($id), $options);
    }
}
