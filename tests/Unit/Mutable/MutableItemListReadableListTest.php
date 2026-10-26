<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Mutable;

use MSSTC4PHP\DtoCollection\Tests\Contract\ReadableListContract;
use MSSTC4PHP\DtoCollection\Tests\Contract\ReadOnlyOffsetsContract;

final class MutableItemListReadableListTest extends ReadableListContract
{
    use MutableItemListLists;
    use ReadOnlyOffsetsContract;
}
