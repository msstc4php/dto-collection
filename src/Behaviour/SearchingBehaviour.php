<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use MSSTC4PHP\DtoCollection\Exception\EmptyCollection;
use MSSTC4PHP\DtoCollection\Exception\IndexOutOfRange;
use MSSTC4PHP\DtoCollection\Exception\NotExactlyOneElement;
use MSSTC4PHP\DtoCollection\Internal\Callback;
use MSSTC4PHP\DtoCollection\Internal\HashSet;
use NoDiscard;

/**
 * @template T
 */
trait SearchingBehaviour
{
    /**
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    public function findPosition(Closure $predicate): ?int
    {
        foreach ($this->elements as $index => $element) {
            if (Callback::invoke($predicate, $element, $index)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    public function any(Closure $predicate): bool
    {
        return $this->findPosition($predicate) !== null;
    }

    /**
     * True on an empty list.
     *
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    public function all(Closure $predicate): bool
    {
        foreach ($this->elements as $index => $element) {
            if (!Callback::invoke($predicate, $element, $index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Closure(T, int): bool $predicate
     */
    #[NoDiscard]
    public function none(Closure $predicate): bool
    {
        return !$this->any($predicate);
    }

    /**
     * @return T|null
     */
    protected function doFirst(): mixed
    {
        return $this->elements[0] ?? null;
    }

    /**
     * @return T|null
     */
    protected function doLast(): mixed
    {
        return $this->elements[count($this->elements) - 1] ?? null;
    }

    /**
     * @return T
     */
    protected function doFirstOrFail(): mixed
    {
        if ($this->elements === []) {
            throw new EmptyCollection('The list is empty: it has no first element');
        }

        return $this->elements[0];
    }

    /**
     * @return T
     */
    protected function doLastOrFail(): mixed
    {
        if ($this->elements === []) {
            throw new EmptyCollection('The list is empty: it has no last element');
        }

        return $this->elements[count($this->elements) - 1];
    }

    /**
     * @return T
     */
    protected function doGetOrFail(int $index): mixed
    {
        if (!array_key_exists($index, $this->elements)) {
            throw IndexOutOfRange::for($index, count($this->elements));
        }

        return $this->elements[$index];
    }

    /**
     * @param int $position a negative position counts from the end: -1 is the last element
     *
     * @return T|null
     */
    protected function doAt(int $position): mixed
    {
        return $this->elements[$position < 0 ? count($this->elements) + $position : $position] ?? null;
    }

    /**
     * @return T
     */
    protected function doSingle(): mixed
    {
        if (count($this->elements) !== 1) {
            throw new NotExactlyOneElement(sprintf('Expected exactly one element, got %d', count($this->elements)));
        }

        return $this->elements[0];
    }

    /**
     * @return T|null
     */
    protected function doSingleOrNull(): mixed
    {
        return count($this->elements) === 1 ? $this->elements[0] : null;
    }

    /**
     * @return T|null
     */
    protected function doRandom(): mixed
    {
        return $this->elements === [] ? null : $this->elements[array_rand($this->elements)];
    }

    /**
     * @param T $element
     */
    protected function doContains(mixed $element): bool
    {
        return $this->doPositionOf($element) !== null;
    }

    /**
     * True when no element is given.
     *
     * @param T ...$elements
     */
    protected function doContainsAll(mixed ...$elements): bool
    {
        $members = new HashSet($this->equality(), $this->elements);
        foreach ($elements as $element) {
            if (!$members->contains($element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * False when no element is given.
     *
     * @param T ...$elements
     */
    protected function doContainsAny(mixed ...$elements): bool
    {
        $members = new HashSet($this->equality(), $this->elements);
        foreach ($elements as $element) {
            if ($members->contains($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param T $element
     */
    protected function doPositionOf(mixed $element): ?int
    {
        $equality = $this->equality();
        foreach ($this->elements as $index => $candidate) {
            if ($equality->equals($candidate, $element)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return T|null
     */
    protected function doFind(Closure $predicate): mixed
    {
        $index = $this->findPosition($predicate);

        return $index === null ? null : $this->elements[$index];
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return T|null
     */
    protected function doFindLast(Closure $predicate): mixed
    {
        for ($index = count($this->elements) - 1; $index >= 0; $index--) {
            if (Callback::invoke($predicate, $this->elements[$index], $index)) {
                return $this->elements[$index];
            }
        }

        return null;
    }
}
