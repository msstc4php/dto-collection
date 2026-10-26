<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use OutOfRangeException;

final class IndexOutOfRange extends OutOfRangeException implements CollectionException
{
    public static function for(int $index, int $count): self
    {
        return new self(sprintf('Index %d is out of range for %d %s', $index, $count, $count === 1 ? 'element' : 'elements'));
    }
}
