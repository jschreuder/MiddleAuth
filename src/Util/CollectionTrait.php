<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Util;

use Iterator;

/**
 * Trait for creating type-safe collections of entities.
 *
 * Implementing classes must:
 * 1. Store entities in a private array property named $collection
 * 2. Implement a constructor that accepts variadic entities and assigns them to $this->collection
 *
 * Example implementation:
 * ```
 * final class UserCollection implements IteratorAggregate
 * {
 *     use CollectionTrait;
 *
 *     public function __construct(
 *         private array $collection = [],
 *     ) {}
 * }
 * ```
 */
trait CollectionTrait
{
    private array $collection;

    public function getIterator(): Iterator
    {
        foreach ($this->collection as $item) {
            yield $item;
        }
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    public function toArray(): array
    {
        return $this->collection;
    }
}
