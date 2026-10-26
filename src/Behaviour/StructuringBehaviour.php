<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use BackedEnum;
use Closure;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\Internal\Callback;
use MSSTC4PHP\DtoCollection\Internal\SelectorKey;
use NoDiscard;

/**
 * Splits a list into lists of the same class.
 *
 * @template T
 */
trait StructuringBehaviour
{
    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return array{static, static} the matching elements, then the rest
     */
    #[NoDiscard]
    public function split(Closure $predicate): array
    {
        $matching = [];
        $rest = [];
        foreach ($this->elements as $index => $element) {
            if (Callback::invoke($predicate, $element, $index)) {
                $matching[] = $element;
            } else {
                $rest[] = $element;
            }
        }

        return [$this->copyWith($matching), $this->copyWith($rest)];
    }

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return array<array-key, static> groups in the order their keys are first seen
     */
    #[NoDiscard]
    public function groupBy(Closure $selector): array
    {
        $groups = [];
        foreach ($this->elements as $element) {
            $groups[SelectorKey::of($selector($element), 'groupBy')][] = $element;
        }

        return array_map(fn (array $group): self => $this->copyWith($group), $groups);
    }

    /**
     * @return list<static> the last chunk may be shorter
     *
     * @throws InvalidArgument when $size is below 1
     */
    #[NoDiscard]
    public function chunk(int $size): array
    {
        if ($size < 1) {
            throw new InvalidArgument(sprintf('chunk() needs a size of 1 or more, %d given', $size));
        }

        return array_map(fn (array $chunk): self => $this->copyWith($chunk), array_chunk($this->elements, $size));
    }

    /**
     * Windows of $size elements starting every $step elements; with $partial, the shorter windows at the end too.
     *
     * @return list<static>
     *
     * @throws InvalidArgument when $size or $step is below 1
     */
    #[NoDiscard]
    public function windowed(int $size, int $step = 1, bool $partial = false): array
    {
        if ($size < 1 || $step < 1) {
            throw new InvalidArgument(sprintf('windowed() needs a size and a step of 1 or more, %d and %d given', $size, $step));
        }

        $windows = [];
        $count = count($this->elements);
        for ($start = 0; $start < $count && ($partial || $start + $size <= $count); $start += $step) {
            $windows[] = $this->copyWith(array_slice($this->elements, $start, $size));
        }

        return $windows;
    }

    /**
     * Consecutive runs of elements: a new chunk starts where $continues returns false.
     *
     * @param Closure(T, T): bool $continues receives the previous and the current element
     *
     * @return list<static>
     */
    #[NoDiscard]
    public function chunkWhile(Closure $continues): array
    {
        $chunks = [];
        $chunk = [];
        foreach ($this->elements as $index => $element) {
            if ($index > 0 && !$continues($this->elements[$index - 1], $element)) {
                $chunks[] = $this->copyWith($chunk);
                $chunk = [];
            }
            $chunk[] = $element;
        }
        if ($chunk !== []) {
            $chunks[] = $this->copyWith($chunk);
        }

        return $chunks;
    }

    /**
     * @param Closure(T): (int|string|BackedEnum) $selector
     *
     * @return array<array-key, T> the last element wins a key
     */
    #[NoDiscard]
    public function keyBy(Closure $selector): array
    {
        $keyed = [];
        foreach ($this->elements as $element) {
            $keyed[SelectorKey::of($selector($element), 'keyBy')] = $element;
        }

        return $keyed;
    }
}
