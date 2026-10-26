<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use ArrayAccess;
use BackedEnum;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * A list with keys 0..n-1. Methods whose signature names the element type are typed by each concrete class.
 *
 * Methods that Doctrine's Collection also declares have no native return type here: the ODM variant must stay
 * compatible with the persistent subclass Doctrine ODM generates, which redeclares them (see docs/odm.md).
 * Iteration runs over a snapshot: changes made during a loop are not seen by it.
 *
 * @template T the element type
 *
 * @extends IteratorAggregate<int, T>
 * @extends ArrayAccess<int, T>
 */
interface ReadableList extends IteratorAggregate, Countable, JsonSerializable, ArrayAccess
{
    /**
     * @return bool
     */
    public function isEmpty();

    public function isNotEmpty(): bool;

    /**
     * @return list<T>
     */
    public function toArray();

    /**
     * @return list<T>
     */
    public function jsonSerialize(): array;

    /**
     * Same length and pairwise equal elements under this list's equality.
     *
     * @param ReadableList<T> $other
     */
    public function equals(self $other): bool;

    /**
     * @return T|null
     */
    public function first();

    /**
     * @return T|null
     */
    public function last();

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
    public function get(int $index);

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
    public function reduce(Closure $reducer, mixed $initial = null);

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return non-negative-int
     */
    public function countWhere(Closure $predicate): int;

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
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

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    public function filter(Closure $predicate);

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    public function reject(Closure $predicate): self;

    /**
     * @param Closure(T, int): T $mapper its results are checked against the element type
     *
     * @return static
     */
    public function transform(Closure $mapper): self;

    /**
     * array_slice() semantics: a negative offset counts from the end, a negative length stops that far from the end.
     *
     * No `?int $length = null`: Doctrine ODM's generated subclass would redeclare it implicitly nullable (deprecated in PHP 8.4).
     *
     * @return static
     */
    public function sliced(int $offset, int $length = PHP_INT_MAX): self;

    /**
     * @return static
     *
     * @throws Exception\InvalidArgument on a negative count
     */
    public function take(int $count): self;

    /**
     * @return static
     *
     * @throws Exception\InvalidArgument on a negative count
     */
    public function takeLast(int $count): self;

    /**
     * @return static
     *
     * @throws Exception\InvalidArgument on a negative count
     */
    public function drop(int $count): self;

    /**
     * @return static
     *
     * @throws Exception\InvalidArgument on a negative count
     */
    public function dropLast(int $count): self;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    public function takeWhile(Closure $predicate): self;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    public function dropWhile(Closure $predicate): self;

    /**
     * A stable sort by $comparator.
     *
     * @param Closure(T, T): int $comparator
     *
     * @return static
     */
    public function sorted(Closure $comparator): self;

    /**
     * A stable sort by the values $selector returns, compared with <=>. Chain calls, least significant key first, to sort by several keys.
     *
     * @param Closure(T): mixed $selector
     *
     * @return static
     */
    public function sortedBy(Closure $selector, bool $descending = false): self;

    /**
     * @return static
     */
    public function reversed(): self;

    /**
     * @return static
     */
    public function shuffled(): self;

    /**
     * The first of each group of equal elements.
     *
     * @return static
     */
    public function unique(): self;

    /**
     * The first element for each key.
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return static
     */
    public function uniqueBy(Closure $selector): self;

    /**
     * The elements contained in none of $others; duplicates in this list are kept.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    public function diff(iterable ...$others): self;

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param iterable<T> ...$others
     *
     * @return static
     */
    public function diffBy(Closure $selector, iterable ...$others): self;

    /**
     * The elements contained in every one of $others; a copy when none is given.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    public function intersect(iterable ...$others): self;

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param iterable<T> ...$others
     *
     * @return static
     */
    public function intersectBy(Closure $selector, iterable ...$others): self;

    /**
     * The first of each group of equal elements of this list followed by $others.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    public function union(iterable ...$others): self;

    /**
     * This list followed by $others, duplicates included.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    public function merge(iterable ...$others): self;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return array{static, static} the matching elements, then the rest
     */
    public function split(Closure $predicate): array;

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return array<array-key, static> groups in the order their keys are first seen
     */
    public function groupBy(Closure $selector): array;

    /**
     * @return list<static> the last chunk may be shorter
     *
     * @throws Exception\InvalidArgument when $size is below 1
     */
    public function chunk(int $size): array;

    /**
     * Windows of $size elements starting every $step elements; with $partial, the shorter windows at the end too.
     *
     * @return list<static>
     *
     * @throws Exception\InvalidArgument when $size or $step is below 1
     */
    public function windowed(int $size, int $step = 1, bool $partial = false): array;

    /**
     * @param Closure(T, T): bool $continues receives the previous and the current element
     *
     * @return list<static>
     */
    public function chunkWhile(Closure $continues): array;

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return array<array-key, T> the last element wins a key
     */
    public function keyBy(Closure $selector): array;

    /**
     * @template U
     *
     * @param ElementType<U>|string $type the results are checked against it
     * @param Closure(T, int): U $mapper
     *
     * @return ($type is string ? ListOf<mixed> : ListOf<U>)
     */
    public function mapTo(ElementType|string $type, Closure $mapper): ListOf;

    /**
     * @template U
     *
     * @param ElementType<U>|string $type the results are checked against it
     * @param Closure(T, int): iterable<U> $mapper
     *
     * @return ($type is string ? ListOf<mixed> : ListOf<U>)
     */
    public function flatMapTo(ElementType|string $type, Closure $mapper): ListOf;

    /**
     * @template U
     *
     * @param Closure(T, int): U $mapper
     *
     * @return list<U>
     */
    public function mapToArray(Closure $mapper): array;

    /**
     * @template C of TypedList<mixed>
     *
     * @param class-string<C> $class
     * @param Closure(T, int): mixed $mapper
     *
     * @return C
     */
    public function mapInto(string $class, Closure $mapper): TypedList;

    /**
     * Runs $callback on each element; a callback returning exactly false stops the loop.
     *
     * @param Closure(T, int): mixed $callback
     *
     * @return static this list
     */
    public function each(Closure $callback): self;

    /**
     * Passes this list to the first step, and each step's result to the next; without steps, returns this list.
     *
     * The steps stay untyped: each takes the previous step's result, which PHPDoc cannot chain over a variadic.
     */
    public function pipe(Closure ...$steps): mixed;

    /**
     * A mutable copy with the same element type and equality.
     *
     * @return MutableListOf<T>
     */
    public function toMutable(): MutableListOf;

    /**
     * An immutable copy with the same element type and equality.
     *
     * @return ListOf<T>
     */
    public function toImmutable(): ListOf;
}
