<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Mutable;

use MSSTC4PHP\DtoCollection\Tests\Contract\SetOperationsContract;

final class MutableListOfSetOperationsTest extends SetOperationsContract
{
    use MutableListOfLists;
}
