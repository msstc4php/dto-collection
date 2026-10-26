<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Equality;

/**
 * `===`: scalars by value and type, objects by identity. NAN equals nothing, -0.0 equals 0.0.
 */
final class StrictEquality implements Equality
{
    public function equals(mixed $left, mixed $right): bool
    {
        return $left === $right;
    }

    public function hashKey(mixed $value): ?string
    {
        return match (true) {
            $value === null => 'n',
            is_bool($value) => $value ? 'b:1' : 'b:0',
            is_int($value) => 'i:' . $value,
            is_string($value) => 's:' . $value,
            is_float($value) => $this->floatKey($value),
            is_object($value) => 'o:' . spl_object_id($value),
            default => null,
        };
    }

    private function floatKey(float $value): ?string
    {
        if (is_nan($value)) {
            return null;
        }

        // -0.0 === 0.0 holds, but var_export() spells them differently.
        return 'f:' . ($value === 0.0 ? '0' : var_export($value, true));
    }
}
