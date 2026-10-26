<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use InvalidArgumentException;

final class InvalidElementType extends InvalidArgumentException implements CollectionException
{
}
