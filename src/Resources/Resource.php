<?php

declare(strict_types=1);

namespace Wajub\Resources;

use GuzzleHttp\Client;
use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\Objects\ApiObject;
use Wajub\RequestOptions;

/**
 * Resource exposing `create`, `retrieve` and `list` — the three endpoints every
 * collection in the merchant API supports (`POST /`, `GET /{uid}`, `GET /`).
 */
class Resource extends BaseClient
{
    /**
     * @param  class-string<ApiObject>  $objectClass
     */
    public function __construct(
        string $apiKey,
        string $baseUrl,
        protected readonly string $path,
        protected readonly string $singular,
        protected readonly string $plural,
        ?string $idempotencyKeyPrefix = 'wajub',
        ?Client $http = null,
        protected string $objectClass = ApiObject::class,
        ?int $maxNetworkRetries = null,
    ) {
        parent::__construct($apiKey, $baseUrl, $idempotencyKeyPrefix, $http, $maxNetworkRetries);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function create(array $params, ?RequestOptions $options = null): ApiObject
    {
        $res = $this->post("/{$this->path}", $params, $options);

        return $this->toObject(HttpUtils::pickResource($res, $this->singular));
    }

    public function retrieve(string $id): ApiObject
    {
        $res = $this->get("/{$this->path}/".rawurlencode($id));

        return $this->toObject(HttpUtils::pickResource($res, $this->singular));
    }

    /**
     * @param  array<string, mixed>|null  $params
     */
    public function list(?array $params = null): PagedResult
    {
        return Pagination::createPagedList($this, "/{$this->path}", $this->plural, $params);
    }

    /** @param  array<string, mixed>  $data */
    protected function toObject(array $data): ApiObject
    {
        return $this->objectClass::from($data);
    }
}
