<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Mutable;

use MSSTC4PHP\DtoCollection\Tests\Contract\StructuringMappingContract;

final class MutableListOfStructuringMappingTest extends StructuringMappingContract
{
    use MutableListOfLists;
}
