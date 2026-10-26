<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use NoDiscard;

/**
 * Public element-typed reading methods for the generic lists, typed mixed and checked at runtime.
 *
 * @template T
 */
trait GenericReadingFacade
{
    /**
     * @return T|null
     */
    #[NoDiscard]
    public function get(int $index): mixed
    {
        return $this->doGet($index);
    }

    /**
     * @return T|null
     */
    #[NoDiscard]
    public function first(): mixed
    {
        return $this->doFirst();
    }

    /**
     * @return T|null
     */
    #[NoDiscard]
    public function last(): mixed
    {
        return $this->doLast();
    }

    /**
     * @return T
     */
    #[NoDiscard]
    public function firstOrFail(): mixed
    {
        return $this->doFirstOrFail();
    }

    /**
     * @return T
     */
    #[NoDiscard]
    public function lastOrFail(): mixed
    {
        return $this->doLastOrFail();
    }

    /**
     * @return T
     */
    #[NoDiscard]
    public function getOrFail(int $index): mixed
    {
        return $this->doGetOrFail($index);
    }

    /**
     * @return T|null
     */
    #[NoDiscard]
    public function at(int $position): mixed
    {
        return $this->doAt($position);
    }

    /**
     * @return T
     */
    #[NoDiscard]
    public function single(): mixed
    {
        return $this->doSingle();
    }

    /**
     * @return T|null
     */
    #[NoDiscard]
    public function singleOrNull(): mixed
    {
        return $this->doSingleOrNull();
    }

    /**
     * @return T|null
     */
    #[NoDiscard]
    public function random(): mixed
    {
        return $this->doRandom();
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return T|null
     */
    #[NoDiscard]
    public function find(Closure $predicate): mixed
    {
        return $this->doFind($predicate);
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return T|null
     */
    #[NoDiscard]
    public function findLast(Closure $predicate): mixed
    {
        return $this->doFindLast($predicate);
    }

    #[NoDiscard]
    public function contains(mixed $element): bool
    {
        return $this->doContains($this->elementType()->coerce($element, 0));
    }

    #[NoDiscard]
    public function containsAll(mixed ...$elements): bool
    {
        return $this->doContainsAll(...$this->coerceAll($elements));
    }

    #[NoDiscard]
    public function containsAny(mixed ...$elements): bool
    {
        return $this->doContainsAny(...$this->coerceAll($elements));
    }

    #[NoDiscard]
    public function positionOf(mixed $element): ?int
    {
        return $this->doPositionOf($this->elementType()->coerce($element, 0));
    }
}
