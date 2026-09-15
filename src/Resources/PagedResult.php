<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Generator;
use Iterator;
use RuntimeException;

/**
 * One page of a list endpoint. Iterating the object walks the current page;
 * `autoPagingIterator()` keeps fetching until the last one.
 *
 * @implements Iterator<int, array<string, mixed>>
 */
final class PagedResult implements Iterator
{
    /** @var callable(): PagedResult */
    private $fetchNext;

    private int $position = 0;

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<string, mixed>|null  $meta
     * @param  callable(): PagedResult  $fetchNext
     */
    public function __construct(
        public readonly array $data,
        public readonly ?array $meta,
        public readonly bool $hasMore,
        callable $fetchNext,
    ) {
        $this->fetchNext = $fetchNext;
    }

    public function getNextPage(): self
    {
        if (! $this->hasMore) {
            throw new RuntimeException('No more pages available');
        }

        return ($this->fetchNext)();
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function autoPagingIterator(): Generator
    {
        $page = $this;
        while (true) {
            foreach ($page->data as $item) {
                yield $item;
            }

            if (! $page->hasMore) {
                return;
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
        $this->position++;
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
