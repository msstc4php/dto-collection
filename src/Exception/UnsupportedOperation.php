<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use LogicException;

final class UnsupportedOperation extends LogicException implements CollectionException
{
}
