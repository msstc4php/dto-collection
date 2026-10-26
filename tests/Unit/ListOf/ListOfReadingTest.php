<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\ReadableList;
use MSSTC4PHP\DtoCollection\Tests\Contract\ReadableListContract;
use MSSTC4PHP\DtoCollection\Tests\Contract\ReadOnlyOffsetsContract;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

final class ListOfReadingTest extends ReadableListContract
{
    use ReadOnlyOffsetsContract;

    protected function items(Item ...$items): ReadableList
    {
        return ListOf::of(Item::class, $items);
    }

    protected function tags(Tag ...$tags): ReadableList
    {
        return ListOf::of(Tag::class, $tags);
    }
}
