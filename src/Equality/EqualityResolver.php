<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Equality;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equatable;

/**
 * Derives the equality of a generic list from its element type: Equatable classes by equals(), the rest strictly.
 */
final class EqualityResolver
{
    /**
     * @param ElementType<mixed> $type
     */
    public static function forType(ElementType $type): Equality
    {
        $equality = null;
        $compare = static fn (Equatable $left, object $right): bool => $left->equals($right);
        foreach ($type->objectBranches() as $classes) {
            $class = $classes[0];
            if (count($classes) === 1 && is_a($class, Equatable::class, true)) {
                $equality = $equality?->with($class, $compare) ?? ComparatorEquality::of($class, $compare);
            }
        }

        return $equality ?? new StrictEquality();
    }
}
