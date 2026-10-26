<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use Closure;

/**
 * A list changed in place. Mutators return void; queries such as filter() still return a new list.
 *
 * @template T
 *
 * @extends ReadableList<T>
 */
interface MutableList extends ReadableList
{
    public function clear(): void;

    /**
     * A stable sort by $comparator.
     *
     * @param Closure(T, T): int $comparator
     */
    public function sort(Closure $comparator): void;

    /**
     * A stable sort by the values $selector returns, compared with <=>.
     *
     * @param Closure(T): mixed $selector
     */
    public function sortBy(Closure $selector, bool $descending = false): void;

    public function reverse(): void;

    /**
     * @throws Exception\IndexOutOfRange
     */
    public function swap(int $first, int $second): void;

    /**
     * Moves the element at $from so that it ends up at $to.
     *
     * @throws Exception\IndexOutOfRange
     */
    public function move(int $from, int $to): void;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return non-negative-int how many elements were removed
     */
    public function removeIf(Closure $predicate): int;

    /**
     * @return T|null the removed element, null when $index is out of range
     */
    public function remove(int $index): mixed;

    /**
     * @return T|null the removed last element, null on an empty list
     */
    public function pop(): mixed;

    /**
     * @return T|null the removed first element, null on an empty list
     */
    public function shift(): mixed;
}
