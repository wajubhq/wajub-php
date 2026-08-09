<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Iterator;

/**
 * @implements Iterator<int, array<string, mixed>>
 */
final class PagedResult implements Iterator
{
    /** @var array<int, array<string, mixed>> */
    public readonly array $data;

    /** @var array<string, mixed>|null */
    public readonly ?array $meta;

    public readonly bool $hasMore;

    /** @var callable(int): PagedResult */
    private $fetchPage;

    private int $position = 0;

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<string, mixed>|null  $meta
     * @param  callable(int): PagedResult  $fetchPage
     */
    public function __construct(array $data, ?array $meta, bool $hasMore, callable $fetchPage)
    {
        $this->data = $data;
        $this->meta = $meta;
        $this->hasMore = $hasMore;
        $this->fetchPage = $fetchPage;
    }

    public function getNextPage(): self
    {
        if (! $this->hasMore) {
            throw new \RuntimeException('No more pages available');
        }

        $next = ((int) ($this->meta['current_page'] ?? 1)) + 1;

        return ($this->fetchPage)($next);
    }

    /**
     * @return \Generator<int, array<string, mixed>>
     */
    public function autoPagingIterator(): \Generator
    {
        $page = $this;
        while (true) {
            foreach ($page->data as $item) {
                yield $item;
            }
            if (! $page->hasMore) {
                break;
            }
            $page = $page->getNextPage();
        }
    }

    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }
}
