<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use InvalidArgumentException;
use MSSTC4PHP\DtoCollection\ElementType;

final class ElementTypeMismatch extends InvalidArgumentException implements CollectionException
{
    /**
     * @param ElementType<mixed> $expected
     */
    public static function at(int $index, ElementType $expected, mixed $actual): self
    {
        return new self(sprintf('Element #%d must be %s, %s given', $index, $expected->describe(), get_debug_type($actual)));
    }
}
