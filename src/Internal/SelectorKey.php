<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Internal;

use BackedEnum;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;

/**
 * Checks what a key selector returned before it becomes an array key: PHP would truncate a float, turn a bool
 * into 0 or 1 and refuse anything else.
 *
 * @internal
 */
final class SelectorKey
{
    /**
     * @throws InvalidArgument
     */
    public static function of(mixed $key, string $method): int|string
    {
        if ($key instanceof BackedEnum) {
            return $key->value;
        }
        if (!is_int($key) && !is_string($key)) {
            throw new InvalidArgument(sprintf('%s() needs a selector that returns int or string, %s given', $method, get_debug_type($key)));
        }

        return $key;
    }
}
