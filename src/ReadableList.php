<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use ArrayAccess;
use ArrayIterator;
use Closure;
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

    /**
     * @return T|null
     */
    public function first(): mixed;

    /**
     * @return T|null
     */
    public function last(): mixed;

    /**
     * @return T
     *
     * @throws Exception\EmptyCollection
     */
    public function firstOrFail(): mixed;

    /**
     * @return T
     *
     * @throws Exception\EmptyCollection
     */
    public function lastOrFail(): mixed;

    /**
     * @return T|null
     */
    public function get(int $index): mixed;

    /**
     * @return T
     *
     * @throws Exception\IndexOutOfRange
     */
    public function getOrFail(int $index): mixed;

    /**
     * @param int $position a negative position counts from the end: -1 is the last element
     *
     * @return T|null
     */
    public function at(int $position): mixed;

    /**
     * @return T
     *
     * @throws Exception\NotExactlyOneElement
     */
    public function single(): mixed;

    /**
     * @return T|null null unless the list has exactly one element
     */
    public function singleOrNull(): mixed;

    /**
     * @return T|null
     */
    public function random(): mixed;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return T|null
     */
    public function find(Closure $predicate): mixed;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return T|null
     */
    public function findLast(Closure $predicate): mixed;

    /**
     * @param Closure(T, int): bool $predicate
     */
    public function findPosition(Closure $predicate): ?int;

    /**
     * @param Closure(T, int): bool $predicate
     */
    public function any(Closure $predicate): bool;

    /**
     * True on an empty list.
     *
     * @param Closure(T, int): bool $predicate
     */
    public function all(Closure $predicate): bool;

    /**
     * @param Closure(T, int): bool $predicate
     */
    public function none(Closure $predicate): bool;

    /**
     * @template R
     *
     * @param Closure(R, T, int): R $reducer the index is passed when the reducer declares a third parameter
     * @param R $initial
     *
     * @return R
     */
    public function reduce(Closure $reducer, mixed $initial = null): mixed;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return non-negative-int
     */
    public function countWhere(Closure $predicate): int;

    /**
     * @param Closure(T): array-key $selector
     *
     * @return array<array-key, positive-int> keys in the order they are first seen
     */
    public function countBy(Closure $selector): array;

    /**
     * @param Closure(T): (int|float) $selector
     */
    public function sumBy(Closure $selector): int|float;

    /**
     * @param Closure(T): (int|float) $selector
     */
    public function averageBy(Closure $selector): ?float;

    /**
     * @param Closure(T): mixed $selector its results are compared with <=>
     *
     * @return T|null the first of the smallest
     */
    public function minBy(Closure $selector): mixed;

    /**
     * @param Closure(T): mixed $selector its results are compared with <=>
     *
     * @return T|null the first of the largest
     */
    public function maxBy(Closure $selector): mixed;
}
