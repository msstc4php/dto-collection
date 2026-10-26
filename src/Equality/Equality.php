<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Equality;

/**
 * Decides when two elements are the same for contains(), unique(), diff() and the like.
 *
 * Equal values must return the same hash key, or null, which makes set operations compare that value pairwise.
 */
interface Equality
{
    public function equals(mixed $left, mixed $right): bool;

    public function hashKey(mixed $value): ?string;
}
