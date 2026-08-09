<?php

declare(strict_types=1);

namespace Wajub\Objects;

use ArrayAccess;
use JsonSerializable;

/**
 * Base API resource object — array-accessible and JSON-serializable.
 *
 * @implements ArrayAccess<string, mixed>
 */
abstract class ApiObject implements ArrayAccess, JsonSerializable
{
    /** @param  array<string, mixed>  $values */
    public function __construct(protected array $values) {}

    /** @param  array<string, mixed>  $values */
    public static function from(array $values): static
    {
        return new static($values);
    }

    public function __get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->values[$name]);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->values;
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) ? ($this->values[$offset] ?? null) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_string($offset)) {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset)) {
            unset($this->values[$offset]);
        }
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->values;
    }
}
