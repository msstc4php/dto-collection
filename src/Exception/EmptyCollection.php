<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use UnderflowException;

final class EmptyCollection extends UnderflowException implements CollectionException
{
}
