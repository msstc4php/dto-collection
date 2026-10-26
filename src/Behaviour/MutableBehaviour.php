<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use MSSTC4PHP\DtoCollection\Exception\IndexOutOfRange;
use MSSTC4PHP\DtoCollection\Internal\Callback;

/**
 * In-place changes of a mutable list. Every mutator keeps the keys 0..n-1.
 *
 * @template T
 */
trait MutableBehaviour
{
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * @param Closure(T, T): int $comparator
     */
    public function sort(Closure $comparator): void
    {
        usort($this->elements, $comparator);
    }

    /**
     * @param Closure(T): mixed $selector
     */
    public function sortBy(Closure $selector, bool $descending = false): void
    {
        $this->elements = $this->sortedElementsBy($selector, $descending);
    }

    public function reverse(): void
    {
        $this->elements = array_reverse($this->elements);
    }

    /**
     * @throws IndexOutOfRange
     */
    public function swap(int $first, int $second): void
    {
        $this->assertExistingIndex($first);
        $this->assertExistingIndex($second);
        $elements = $this->elements;
        [$elements[$first], $elements[$second]] = [$elements[$second], $elements[$first]];
        $this->elements = array_values($elements);
    }

    /**
     * @throws IndexOutOfRange
     */
    public function move(int $from, int $to): void
    {
        $this->assertExistingIndex($from);
        $this->assertExistingIndex($to);
        $moved = array_splice($this->elements, $from, 1);
        array_splice($this->elements, $to, 0, $moved);
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return non-negative-int
     */
    public function removeIf(Closure $predicate): int
    {
        $kept = [];
        $removed = 0;
        foreach ($this->elements as $index => $element) {
            if (Callback::invoke($predicate, $element, $index)) {
                $removed++;
            } else {
                $kept[] = $element;
            }
        }
        $this->elements = $kept;

        return $removed;
    }

    /**
     * @param T ...$elements
     */
    protected function doPush(mixed ...$elements): void
    {
        $this->elements = [...$this->elements, ...array_values($elements)];
    }

    /**
     * @param T ...$elements
     */
    protected function doUnshift(mixed ...$elements): void
    {
        $this->elements = [...array_values($elements), ...$this->elements];
    }

    /**
     * Replaces the element at $index, or appends when $index is the length.
     *
     * @param T $element
     *
     * @throws IndexOutOfRange
     */
    protected function doSet(int $index, mixed $element): void
    {
        $this->assertIndexUpToCount($index);
        array_splice($this->elements, $index, 1, [$element]);
    }

    /**
     * @param T $element
     *
     * @throws IndexOutOfRange
     */
    protected function doInsertAt(int $index, mixed $element): void
    {
        $this->assertIndexUpToCount($index);
        array_splice($this->elements, $index, 0, [$element]);
    }

    /**
     * @return T|null null when $index is out of range
     */
    protected function doRemove(int $index): mixed
    {
        if (!array_key_exists($index, $this->elements)) {
            return null;
        }

        return array_splice($this->elements, $index, 1)[0];
    }

    /**
     * Removes the first element equal to $element.
     *
     * @param T $element
     */
    protected function doRemoveElement(mixed $element): bool
    {
        $index = $this->doPositionOf($element);
        if ($index === null) {
            return false;
        }
        array_splice($this->elements, $index, 1);

        return true;
    }

    /**
     * @return T|null
     */
    protected function doPop(): mixed
    {
        return array_pop($this->elements);
    }

    /**
     * @return T|null
     */
    protected function doShift(): mixed
    {
        return array_shift($this->elements);
    }

    /**
     * @throws IndexOutOfRange
     */
    private function assertExistingIndex(int $index): void
    {
        if (!array_key_exists($index, $this->elements)) {
            throw IndexOutOfRange::for($index, count($this->elements));
        }
    }

    /**
     * @throws IndexOutOfRange
     */
    private function assertIndexUpToCount(int $index): void
    {
        if ($index < 0 || $index > count($this->elements)) {
            throw IndexOutOfRange::for($index, count($this->elements));
        }
    }
}
