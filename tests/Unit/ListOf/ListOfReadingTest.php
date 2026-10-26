<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\Tests\Contract\ReadableListContract;
use MSSTC4PHP\DtoCollection\Tests\Contract\ReadOnlyOffsetsContract;

final class ListOfReadingTest extends ReadableListContract
{
    use ListOfLists;
    use ReadOnlyOffsetsContract;
}
