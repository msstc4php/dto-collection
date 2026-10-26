<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use Closure;

/**
 * A list that never changes: with*() and without*() return a modified copy.
 *
 * @template T
 *
 * @extends ReadableList<T>
 */
interface ImmutableList extends ReadableList
{
    /**
     * A copy without the elements at $indexes.
     *
     * @return static
     *
     * @throws Exception\IndexOutOfRange
     */
    public function without(int ...$indexes): self;

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    public function withoutIf(Closure $predicate): self;
}
