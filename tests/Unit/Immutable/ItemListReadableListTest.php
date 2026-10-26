<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Immutable;

use MSSTC4PHP\DtoCollection\Tests\Contract\ReadableListContract;
use MSSTC4PHP\DtoCollection\Tests\Contract\ReadOnlyOffsetsContract;

final class ItemListReadableListTest extends ReadableListContract
{
    use ItemListLists;
    use ReadOnlyOffsetsContract;
}
