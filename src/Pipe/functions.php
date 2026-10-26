<?php

declare(strict_types=1);

/*
 * One function per query of ReadableList and ImmutableList: each takes the method's arguments and returns a closure
 * over a list, for PHP 8.5's pipe operator (`$list |> filter(...)`) or ReadableList::pipe() on older versions.
 * Methods taking an element are left out: an interface cannot type their parameter. So are the mutators.
 */

namespace MSSTC4PHP\DtoCollection\Pipe;

use BackedEnum;
use Closure;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\ImmutableList;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\MutableListOf;
use MSSTC4PHP\DtoCollection\ReadableList;
use MSSTC4PHP\DtoCollection\TypedList;
use NoDiscard;

if (!\function_exists(__NAMESPACE__ . '\\count')) {
    /**
     * @return Closure<T>(ReadableList<T>): non-negative-int
     */
    #[NoDiscard]
    function count(): Closure
    {
        return static fn (ReadableList $list): int => $list->count();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\isEmpty')) {
    /**
     * @return Closure<T>(ReadableList<T>): bool
     */
    #[NoDiscard]
    function isEmpty(): Closure
    {
        return static fn (ReadableList $list): bool => $list->isEmpty();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\isNotEmpty')) {
    /**
     * @return Closure<T>(ReadableList<T>): bool
     */
    #[NoDiscard]
    function isNotEmpty(): Closure
    {
        return static fn (ReadableList $list): bool => $list->isNotEmpty();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\toArray')) {
    /**
     * @return Closure<T>(ReadableList<T>): list<T>
     */
    #[NoDiscard]
    function toArray(): Closure
    {
        return static fn (ReadableList $list): array => $list->toArray();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\jsonSerialize')) {
    /**
     * @return Closure<T>(ReadableList<T>): list<T>
     */
    #[NoDiscard]
    function jsonSerialize(): Closure
    {
        return static fn (ReadableList $list): array => $list->jsonSerialize();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\equals')) {
    /**
     * @template T
     *
     * @param ReadableList<T> $other
     *
     * @return Closure(ReadableList<T>): bool
     */
    #[NoDiscard]
    function equals(ReadableList $other): Closure
    {
        return static fn (ReadableList $list): bool => $list->equals($other);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\first')) {
    #[NoDiscard]
    function first(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->first();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\last')) {
    #[NoDiscard]
    function last(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->last();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\firstOrFail')) {
    /**
     * @return Closure<T>(ReadableList<T>): T
     */
    #[NoDiscard]
    function firstOrFail(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->firstOrFail();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\lastOrFail')) {
    /**
     * @return Closure<T>(ReadableList<T>): T
     */
    #[NoDiscard]
    function lastOrFail(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->lastOrFail();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\get')) {
    #[NoDiscard]
    function get(int $index): Closure
    {
        return static fn (ReadableList $list): mixed => $list->get($index);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\getOrFail')) {
    /**
     * @return Closure<T>(ReadableList<T>): T
     */
    #[NoDiscard]
    function getOrFail(int $index): Closure
    {
        return static fn (ReadableList $list): mixed => $list->getOrFail($index);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\at')) {
    #[NoDiscard]
    function at(int $position): Closure
    {
        return static fn (ReadableList $list): mixed => $list->at($position);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\single')) {
    /**
     * @return Closure<T>(ReadableList<T>): T
     */
    #[NoDiscard]
    function single(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->single();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\singleOrNull')) {
    #[NoDiscard]
    function singleOrNull(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->singleOrNull();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\random')) {
    #[NoDiscard]
    function random(): Closure
    {
        return static fn (ReadableList $list): mixed => $list->random();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\find')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    function find(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): mixed => $list->find($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\findLast')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    function findLast(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): mixed => $list->findLast($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\findPosition')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    function findPosition(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): ?int => $list->findPosition($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\any')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): bool
     */
    #[NoDiscard]
    function any(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): bool => $list->any($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\all')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): bool
     */
    #[NoDiscard]
    function all(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): bool => $list->all($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\none')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): bool
     */
    #[NoDiscard]
    function none(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): bool => $list->none($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\reduce')) {
    /**
     * @template T
     * @template R
     *
     * @param Closure(R, T, int): R $reducer
     * @param R $initial
     *
     * @return Closure(ReadableList<T>): R
     */
    #[NoDiscard]
    function reduce(Closure $reducer, mixed $initial = null): Closure
    {
        return static fn (ReadableList $list): mixed => $list->reduce($reducer, $initial);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\countWhere')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): non-negative-int
     */
    #[NoDiscard]
    function countWhere(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): int => $list->countWhere($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\countBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return Closure(ReadableList<T>): array<array-key, positive-int>
     */
    #[NoDiscard]
    function countBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): array => $list->countBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\sumBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|float) $selector
     *
     * @return Closure(ReadableList<T>): (int|float)
     */
    #[NoDiscard]
    function sumBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): int|float => $list->sumBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\averageBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|float) $selector
     */
    #[NoDiscard]
    function averageBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): ?float => $list->averageBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\minBy')) {
    /**
     * @template T
     *
     * @param Closure(T): mixed $selector
     */
    #[NoDiscard]
    function minBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): mixed => $list->minBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\maxBy')) {
    /**
     * @template T
     *
     * @param Closure(T): mixed $selector
     */
    #[NoDiscard]
    function maxBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): mixed => $list->maxBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\filter')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function filter(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->filter($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\reject')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function reject(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->reject($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\transform')) {
    /**
     * @template T
     *
     * @param Closure(T, int): T $mapper
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function transform(Closure $mapper): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->transform($mapper);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\sliced')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function sliced(int $offset, int $length = PHP_INT_MAX): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->sliced($offset, $length);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\take')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function take(int $count): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->take($count);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\takeLast')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function takeLast(int $count): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->takeLast($count);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\drop')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function drop(int $count): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->drop($count);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\dropLast')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function dropLast(int $count): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->dropLast($count);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\takeWhile')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function takeWhile(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->takeWhile($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\dropWhile')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function dropWhile(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->dropWhile($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\sorted')) {
    /**
     * @template T
     *
     * @param Closure(T, T): int $comparator
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function sorted(Closure $comparator): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->sorted($comparator);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\sortedBy')) {
    /**
     * @template T
     *
     * @param Closure(T): mixed $selector
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function sortedBy(Closure $selector, bool $descending = false): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->sortedBy($selector, $descending);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\reversed')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function reversed(): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->reversed();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\shuffled')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function shuffled(): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->shuffled();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\unique')) {
    /**
     * @return Closure<T>(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function unique(): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->unique();
    }
}

if (!\function_exists(__NAMESPACE__ . '\\uniqueBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function uniqueBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->uniqueBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\diff')) {
    /**
     * @template T
     *
     * @param iterable<T> ...$others
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function diff(iterable ...$others): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->diff(...$others);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\diffBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param iterable<T> ...$others
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function diffBy(Closure $selector, iterable ...$others): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->diffBy($selector, ...$others);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\intersect')) {
    /**
     * @template T
     *
     * @param iterable<T> ...$others
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function intersect(iterable ...$others): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->intersect(...$others);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\intersectBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param iterable<T> ...$others
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function intersectBy(Closure $selector, iterable ...$others): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->intersectBy($selector, ...$others);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\union')) {
    /**
     * @template T
     *
     * @param iterable<T> ...$others
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function union(iterable ...$others): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->union(...$others);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\merge')) {
    /**
     * @template T
     *
     * @param iterable<T> ...$others
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function merge(iterable ...$others): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->merge(...$others);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\split')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ReadableList<T>): array{ReadableList<T>, ReadableList<T>}
     */
    #[NoDiscard]
    function split(Closure $predicate): Closure
    {
        return static fn (ReadableList $list): array => $list->split($predicate);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\groupBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return Closure(ReadableList<T>): array<array-key, ReadableList<T>>
     */
    #[NoDiscard]
    function groupBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): array => $list->groupBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\chunk')) {
    /**
     * @return Closure<T>(ReadableList<T>): list<ReadableList<T>>
     */
    #[NoDiscard]
    function chunk(int $size): Closure
    {
        return static fn (ReadableList $list): array => $list->chunk($size);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\windowed')) {
    /**
     * @return Closure<T>(ReadableList<T>): list<ReadableList<T>>
     */
    #[NoDiscard]
    function windowed(int $size, int $step = 1, bool $partial = false): Closure
    {
        return static fn (ReadableList $list): array => $list->windowed($size, $step, $partial);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\chunkWhile')) {
    /**
     * @template T
     *
     * @param Closure(T, T): bool $continues
     *
     * @return Closure(ReadableList<T>): list<ReadableList<T>>
     */
    #[NoDiscard]
    function chunkWhile(Closure $continues): Closure
    {
        return static fn (ReadableList $list): array => $list->chunkWhile($continues);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\keyBy')) {
    /**
     * @template T
     *
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return Closure(ReadableList<T>): array<array-key, T>
     */
    #[NoDiscard]
    function keyBy(Closure $selector): Closure
    {
        return static fn (ReadableList $list): array => $list->keyBy($selector);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\mapTo')) {
    /**
     * @template T
     * @template U
     *
     * @param ElementType<U>|string $type
     * @param Closure(T, int): U $mapper
     *
     * @return ($type is string ? Closure(ReadableList<T>): ListOf<mixed> : Closure(ReadableList<T>): ListOf<U>)
     */
    #[NoDiscard]
    function mapTo(ElementType|string $type, Closure $mapper): Closure
    {
        return static fn (ReadableList $list): ListOf => $list->mapTo($type, $mapper);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\flatMapTo')) {
    /**
     * @template T
     * @template U
     *
     * @param ElementType<U>|string $type
     * @param Closure(T, int): iterable<U> $mapper
     *
     * @return ($type is string ? Closure(ReadableList<T>): ListOf<mixed> : Closure(ReadableList<T>): ListOf<U>)
     */
    #[NoDiscard]
    function flatMapTo(ElementType|string $type, Closure $mapper): Closure
    {
        return static fn (ReadableList $list): ListOf => $list->flatMapTo($type, $mapper);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\mapToArray')) {
    /**
     * @template T
     * @template U
     *
     * @param Closure(T, int): U $mapper
     *
     * @return Closure(ReadableList<T>): list<U>
     */
    #[NoDiscard]
    function mapToArray(Closure $mapper): Closure
    {
        return static fn (ReadableList $list): array => $list->mapToArray($mapper);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\mapInto')) {
    /**
     * @template T
     * @template C of TypedList<mixed>
     *
     * @param class-string<C> $class
     * @param Closure(T, int): mixed $mapper
     *
     * @return Closure(ReadableList<T>): C
     */
    #[NoDiscard]
    function mapInto(string $class, Closure $mapper): Closure
    {
        return static fn (ReadableList $list): TypedList => $list->mapInto($class, $mapper);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\each')) {
    /**
     * @template T
     *
     * @param Closure(T, int): mixed $callback
     *
     * @return Closure(ReadableList<T>): ReadableList<T>
     */
    #[NoDiscard]
    function each(Closure $callback): Closure
    {
        return static fn (ReadableList $list): ReadableList => $list->each($callback);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\toMutable')) {
    /**
     * @return Closure<T>(ReadableList<T>): MutableListOf<T>
     */
    #[NoDiscard]
    function toMutable(): Closure
    {
        // Reason: PHPStan does not infer a generic closure from an arrow function; the method keeps the element type.
        return static fn (ReadableList $list): MutableListOf => $list->toMutable(); // @phpstan-ignore return.type
    }
}

if (!\function_exists(__NAMESPACE__ . '\\toImmutable')) {
    /**
     * @return Closure<T>(ReadableList<T>): ListOf<T>
     */
    #[NoDiscard]
    function toImmutable(): Closure
    {
        // Reason: PHPStan does not infer a generic closure from an arrow function; the method keeps the element type.
        return static fn (ReadableList $list): ListOf => $list->toImmutable(); // @phpstan-ignore return.type
    }
}

if (!\function_exists(__NAMESPACE__ . '\\without')) {
    /**
     * @return Closure<T>(ImmutableList<T>): ImmutableList<T>
     */
    #[NoDiscard]
    function without(int ...$indexes): Closure
    {
        return static fn (ImmutableList $list): ImmutableList => $list->without(...$indexes);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\withoutIf')) {
    /**
     * @template T
     *
     * @param Closure(T, int): bool $predicate
     *
     * @return Closure(ImmutableList<T>): ImmutableList<T>
     */
    #[NoDiscard]
    function withoutIf(Closure $predicate): Closure
    {
        return static fn (ImmutableList $list): ImmutableList => $list->withoutIf($predicate);
    }
}
