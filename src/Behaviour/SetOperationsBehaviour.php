<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use MSSTC4PHP\DtoCollection\Internal\HashSet;
use NoDiscard;

/**
 * Set operations under this list's equality. Their own order and duplicates are kept unless the method says otherwise.
 *
 * @template T
 */
trait SetOperationsBehaviour
{
    /**
     * The first of each group of equal elements.
     *
     * @return static
     */
    #[NoDiscard]
    public function unique(): self
    {
        return $this->copyWith($this->uniqueElements($this->elements));
    }

    /**
     * The first element for each key.
     *
     * @param Closure(T): array-key $selector
     *
     * @return static
     */
    #[NoDiscard]
    public function uniqueBy(Closure $selector): self
    {
        $unique = [];
        foreach ($this->elements as $element) {
            $key = $selector($element);
            if (!array_key_exists($key, $unique)) {
                $unique[$key] = $element;
            }
        }

        return $this->copyWith(array_values($unique));
    }

    /**
     * The elements contained in none of $others.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function diff(iterable ...$others): self
    {
        $excluded = new HashSet($this->equality(), $this->mergedOthers($others));

        return $this->copyWith(array_values(array_filter($this->elements, static fn (mixed $element): bool => !$excluded->contains($element))));
    }

    /**
     * The elements whose key is the key of no element of $others.
     *
     * @param Closure(T): array-key $selector
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function diffBy(Closure $selector, iterable ...$others): self
    {
        $excluded = array_flip(array_map($selector, $this->mergedOthers($others)));

        return $this->copyWith(array_values(array_filter($this->elements, static fn (mixed $element): bool => !isset($excluded[$selector($element)]))));
    }

    /**
     * The elements contained in every one of $others; a copy when none is given.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function intersect(iterable ...$others): self
    {
        $elements = $this->elements;
        foreach ($others as $other) {
            $included = new HashSet($this->equality(), $this->coerceAll($other));
            $elements = array_values(array_filter($elements, $included->contains(...)));
        }

        return $this->copyWith($elements);
    }

    /**
     * The elements whose key is a key in every one of $others; a copy when none is given.
     *
     * @param Closure(T): array-key $selector
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function intersectBy(Closure $selector, iterable ...$others): self
    {
        $elements = $this->elements;
        foreach ($others as $other) {
            $included = array_flip(array_map($selector, $this->coerceAll($other)));
            $elements = array_values(array_filter($elements, static fn (mixed $element): bool => isset($included[$selector($element)])));
        }

        return $this->copyWith($elements);
    }

    /**
     * The first of each group of equal elements of this list followed by $others.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function union(iterable ...$others): self
    {
        return $this->copyWith($this->uniqueElements([...$this->elements, ...$this->mergedOthers($others)]));
    }

    /**
     * This list followed by $others, duplicates included.
     *
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function merge(iterable ...$others): self
    {
        return $this->copyWith([...$this->elements, ...$this->mergedOthers($others)]);
    }

    /**
     * @param array<iterable<mixed>> $others
     *
     * @return list<T>
     */
    private function mergedOthers(array $others): array
    {
        $merged = [];
        foreach ($others as $other) {
            $merged = [...$merged, ...$this->coerceAll($other)];
        }

        return $merged;
    }

    /**
     * @param list<T> $elements
     *
     * @return list<T>
     */
    private function uniqueElements(array $elements): array
    {
        $seen = new HashSet($this->equality(), []);
        $unique = [];
        foreach ($elements as $element) {
            if (!$seen->contains($element)) {
                $seen->add($element);
                $unique[] = $element;
            }
        }

        return $unique;
    }
}
