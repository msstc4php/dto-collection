<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use InvalidArgumentException;

final class InvalidArgument extends InvalidArgumentException implements CollectionException
{
}
