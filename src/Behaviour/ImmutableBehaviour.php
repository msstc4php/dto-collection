<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use MSSTC4PHP\DtoCollection\Exception\IndexOutOfRange;
use NoDiscard;

/**
 * Modified copies of an immutable list.
 *
 * @template T
 */
trait ImmutableBehaviour
{
    /**
     * @return static
     *
     * @throws IndexOutOfRange
     */
    #[NoDiscard]
    public function without(int ...$indexes): self
    {
        $elements = $this->elements;
        foreach ($indexes as $index) {
            if (!array_key_exists($index, $this->elements)) {
                throw IndexOutOfRange::for($index, count($this->elements));
            }
            unset($elements[$index]);
        }

        return $this->copyWith(array_values($elements));
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    #[NoDiscard]
    public function withoutIf(Closure $predicate): self
    {
        return $this->reject($predicate);
    }

    /**
     * @param T ...$elements
     *
     * @return static
     */
    protected function doWith(mixed ...$elements): self
    {
        return $this->copyWith([...$this->elements, ...array_values($elements)]);
    }

    /**
     * @param T ...$elements
     *
     * @return static
     */
    protected function doWithPrepended(mixed ...$elements): self
    {
        return $this->copyWith([...array_values($elements), ...$this->elements]);
    }

    /**
     * Replaces the element at $index, or appends when $index is the length.
     *
     * @param T $element
     *
     * @return static
     *
     * @throws IndexOutOfRange
     */
    protected function doWithSet(int $index, mixed $element): self
    {
        $this->assertIndexUpToLength($index);
        $elements = $this->elements;
        array_splice($elements, $index, 1, [$element]);

        return $this->copyWith($elements);
    }

    /**
     * @param T $element
     *
     * @return static
     *
     * @throws IndexOutOfRange
     */
    protected function doWithInsertedAt(int $index, mixed $element): self
    {
        $this->assertIndexUpToLength($index);
        $elements = $this->elements;
        array_splice($elements, $index, 0, [$element]);

        return $this->copyWith($elements);
    }

    /**
     * Each argument removes the first element equal to it.
     *
     * @param T ...$elements
     *
     * @return static
     */
    protected function doWithoutElement(mixed ...$elements): self
    {
        $equality = $this->equality();
        $remaining = $this->elements;
        foreach ($elements as $removed) {
            foreach ($remaining as $index => $candidate) {
                if ($equality->equals($candidate, $removed)) {
                    unset($remaining[$index]);

                    break;
                }
            }
        }

        return $this->copyWith(array_values($remaining));
    }

    /**
     * Replaces every element equal to $old.
     *
     * @param T $old
     * @param T $new
     *
     * @return static
     */
    protected function doWithReplaced(mixed $old, mixed $new): self
    {
        $equality = $this->equality();

        return $this->copyWith(array_map(static fn (mixed $element): mixed => $equality->equals($element, $old) ? $new : $element, $this->elements));
    }

    /**
     * @throws IndexOutOfRange
     */
    private function assertIndexUpToLength(int $index): void
    {
        if ($index < 0 || $index > count($this->elements)) {
            throw IndexOutOfRange::for($index, count($this->elements));
        }
    }
}
