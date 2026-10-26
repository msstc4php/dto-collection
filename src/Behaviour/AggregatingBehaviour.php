<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use BackedEnum;
use Closure;
use MSSTC4PHP\DtoCollection\Internal\Callback;
use MSSTC4PHP\DtoCollection\Internal\SelectorKey;
use NoDiscard;

/**
 * @template T
 */
trait AggregatingBehaviour
{
    /**
     * Doctrine's signature; the index is passed to a reducer that declares a third parameter.
     *
     * @template R
     *
     * @param Closure(R, T, int): R $reducer
     * @param R $initial
     *
     * @return R
     */
    #[NoDiscard]
    public function reduce(Closure $reducer, mixed $initial = null): mixed
    {
        return $this->reduced($reducer, $initial);
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return non-negative-int
     */
    #[NoDiscard]
    public function countWhere(Closure $predicate): int
    {
        $count = 0;
        foreach ($this->elements as $index => $element) {
            if (Callback::invoke($predicate, $element, $index)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return array<array-key, positive-int> keys in the order they are first seen
     */
    #[NoDiscard]
    public function countBy(Closure $selector): array
    {
        $counts = [];
        foreach ($this->elements as $element) {
            $key = SelectorKey::of($selector($element), 'countBy');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @param Closure(T): (int|float) $selector
     */
    #[NoDiscard]
    public function sumBy(Closure $selector): int|float
    {
        $sum = 0;
        foreach ($this->elements as $element) {
            $sum += $selector($element);
        }

        return $sum;
    }

    /**
     * @param Closure(T): (int|float) $selector
     *
     * @return float|null null on an empty list
     */
    #[NoDiscard]
    public function averageBy(Closure $selector): ?float
    {
        return $this->elements === [] ? null : $this->sumBy($selector) / count($this->elements);
    }

    /**
     * @param Closure(T): mixed $selector its results are compared with <=>
     *
     * @return T|null the first of the smallest
     */
    protected function doMinBy(Closure $selector): mixed
    {
        return $this->extremeBy($selector, -1);
    }

    /**
     * @param Closure(T): mixed $selector its results are compared with <=>
     *
     * @return T|null the first of the largest
     */
    protected function doMaxBy(Closure $selector): mixed
    {
        return $this->extremeBy($selector, 1);
    }

    /**
     * @param Closure(T): mixed $selector
     * @param -1|1 $direction
     *
     * @return T|null
     */
    private function extremeBy(Closure $selector, int $direction): mixed
    {
        $best = null;
        $bestKey = null;
        foreach ($this->elements as $index => $element) {
            $key = $selector($element);
            if ($index === 0 || ($key <=> $bestKey) === $direction) {
                $best = $element;
                $bestKey = $key;
            }
        }

        return $best;
    }

    /**
     * @template R
     *
     * @param Closure(R, T, int): R $reducer
     * @param R $initial
     *
     * @return R
     */
    private function reduced(Closure $reducer, mixed $initial): mixed
    {
        $withIndex = Callback::takes($reducer, 3);
        $carry = $initial;
        foreach ($this->elements as $index => $element) {
            // Reason: a reducer that does not declare the index gets two arguments, as Doctrine's reduce() passes.
            $carry = $withIndex ? $reducer($carry, $element, $index) : $reducer($carry, $element); // @phpstan-ignore arguments.count
        }

        return $carry;
    }
}
