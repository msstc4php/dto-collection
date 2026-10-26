<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use NoDiscard;

/**
 * @template T
 */
trait OrderingBehaviour
{
    /**
     * A stable sort by $comparator.
     *
     * @param Closure(T, T): int $comparator
     *
     * @return static
     */
    #[NoDiscard]
    public function sorted(Closure $comparator): self
    {
        $elements = $this->elements;
        usort($elements, $comparator);

        return $this->copyWith($elements);
    }

    /**
     * A stable sort by the values $selector returns, compared with <=>. Equal keys keep their order, also when descending.
     *
     * Sort by several keys by chaining, least significant key first:
     * `->sortedBy(fn ($user) => $user->name)->sortedBy(fn ($user) => $user->age)` orders by age, then name.
     *
     * @param Closure(T): mixed $selector
     *
     * @return static
     */
    #[NoDiscard]
    public function sortedBy(Closure $selector, bool $descending = false): self
    {
        return $this->copyWith($this->sortedElementsBy($selector, $descending));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function reversed(): self
    {
        return $this->copyWith(array_reverse($this->elements));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function shuffled(): self
    {
        $elements = $this->elements;
        shuffle($elements);

        return $this->copyWith($elements);
    }

    /**
     * @param Closure(T): mixed $selector
     *
     * @return list<T>
     */
    private function sortedElementsBy(Closure $selector, bool $descending): array
    {
        $keys = array_map($selector, $this->elements);
        $positions = array_keys($this->elements);
        $direction = $descending ? -1 : 1;
        usort($positions, static function (int $left, int $right) use ($keys, $direction): int {
            $order = $direction * ($keys[$left] <=> $keys[$right]);

            return $order !== 0 ? $order : $left <=> $right;
        });

        return array_map(fn (int $position): mixed => $this->elements[$position], $positions);
    }
}
