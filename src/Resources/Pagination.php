<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;

final class Pagination
{
    /**
     * @param  array<string, mixed>|null  $params
     */
    public static function createPagedList(
        BaseClient $client,
        string $path,
        string $pluralKey,
        ?array $params = null,
    ): PagedResult {
        /** @var callable(int): PagedResult|null $fetchPage */
        $fetchPage = null;
        $fetchPage = static function (int $pageNum) use ($client, $path, $pluralKey, $params, &$fetchPage): PagedResult {
            $query = array_merge($params ?? [], ['page' => $pageNum]);
            $res = $client->get($path, $query);
            $picked = HttpUtils::pickList($res, $pluralKey);
            $meta = $picked['meta'];
            $hasMore = $meta !== null
                && isset($meta['current_page'], $meta['last_page'])
                && (int) $meta['current_page'] < (int) $meta['last_page'];

            return new PagedResult($picked['data'], $meta, $hasMore, static function (int $next) use (&$fetchPage): PagedResult {
                return $fetchPage($next);
            });
        };

        return $fetchPage((int) ($params['page'] ?? 1));
    }
}
