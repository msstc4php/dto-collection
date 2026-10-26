<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Equality;

/**
 * Decides when two elements are the same for contains(), unique(), diff() and the like.
 *
 * Equal values must return the same hash key, or both null: a value without a key is compared pairwise, and only
 * with the other values without a key.
 */
interface Equality
{
    public function equals(mixed $left, mixed $right): bool;

    public function hashKey(mixed $value): ?string;
}
