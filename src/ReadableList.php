<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * A list with keys 0..n-1. Methods whose signature names the element type are typed by each concrete class.
 *
 * @template T the element type
 *
 * @extends IteratorAggregate<int, T>
 * @extends ArrayAccess<int, T>
 */
interface ReadableList extends IteratorAggregate, Countable, JsonSerializable, ArrayAccess
{
    /**
     * @return non-negative-int
     */
    public function count(): int;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    /**
     * @return list<T>
     */
    public function toArray(): array;

    /**
     * @return list<T>
     */
    public function jsonSerialize(): array;

    /**
     * Iterates a snapshot: changes made during the loop are not seen by it.
     *
     * @return ArrayIterator<int, T>
     */
    public function getIterator(): ArrayIterator;

    /**
     * Same length and pairwise equal elements under this list's equality.
     *
     * @param ReadableList<T> $other
     */
    public function equals(self $other): bool;
}
