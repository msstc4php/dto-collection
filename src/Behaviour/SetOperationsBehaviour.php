<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use BackedEnum;
use Closure;
use MSSTC4PHP\DtoCollection\Internal\HashSet;
use MSSTC4PHP\DtoCollection\Internal\SelectorKey;
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
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return static
     */
    #[NoDiscard]
    public function uniqueBy(Closure $selector): self
    {
        $unique = [];
        foreach ($this->elements as $element) {
            $key = SelectorKey::of($selector($element), 'uniqueBy');
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
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function diffBy(Closure $selector, iterable ...$others): self
    {
        $excluded = array_flip($this->selectorKeys($selector, $this->mergedOthers($others), 'diffBy'));
        $kept = [];
        foreach ($this->elements as $element) {
            if (!array_key_exists(SelectorKey::of($selector($element), 'diffBy'), $excluded)) {
                $kept[] = $element;
            }
        }

        return $this->copyWith($kept);
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
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param iterable<T> ...$others
     *
     * @return static
     */
    #[NoDiscard]
    public function intersectBy(Closure $selector, iterable ...$others): self
    {
        $elements = $this->elements;
        foreach ($others as $other) {
            $included = array_flip($this->selectorKeys($selector, $this->coerceAll($other), 'intersectBy'));
            $kept = [];
            foreach ($elements as $element) {
                if (array_key_exists(SelectorKey::of($selector($element), 'intersectBy'), $included)) {
                    $kept[] = $element;
                }
            }
            $elements = $kept;
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

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     * @param list<T> $elements
     *
     * @return list<array-key>
     */
    private function selectorKeys(Closure $selector, array $elements, string $method): array
    {
        $keys = [];
        foreach ($elements as $element) {
            $keys[] = SelectorKey::of($selector($element), $method);
        }

        return $keys;
    }
}
