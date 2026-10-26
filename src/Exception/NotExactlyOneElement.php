<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Exception;

use LengthException;

final class NotExactlyOneElement extends LengthException implements CollectionException
{
}
