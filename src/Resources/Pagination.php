<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;

/**
 * Builds a PagedResult from a list endpoint.
 *
 * The API paginates by page number (`page`, `meta.current_page`, `meta.last_page`)
 * unless the request carries a `cursor`, in which case it answers with
 * `meta.next_cursor` and `meta.has_more`. Both modes are handled here.
 */
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
        $fetch = static function (array $query) use ($client, $path, $pluralKey, &$fetch): PagedResult {
            $picked = HttpUtils::pickList($client->get($path, $query), $pluralKey);
            $meta = $picked['meta'];

            $nextQuery = self::nextQuery($query, $meta);

            return new PagedResult(
                $picked['data'],
                $meta,
                $nextQuery !== null,
                static fn (): PagedResult => $fetch($nextQuery ?? []),
            );
        };

        return $fetch($params ?? []);
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>|null
     */
    private static function nextQuery(array $query, ?array $meta): ?array
    {
        if ($meta === null) {
            return null;
        }

        if (array_key_exists('next_cursor', $meta)) {
            if (! ($meta['has_more'] ?? false) || ! is_string($meta['next_cursor'])) {
                return null;
            }

            return ['cursor' => $meta['next_cursor']] + $query;
        }

        if (! isset($meta['current_page'], $meta['last_page'])) {
            return null;
        }

        $current = (int) $meta['current_page'];
        if ($current >= (int) $meta['last_page']) {
            return null;
        }

        return ['page' => $current + 1] + $query;
    }
}
