<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Mutable;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\MutableListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

trait MutableListOfLists
{
    /**
     * @return MutableListOf<Item>
     */
    protected function items(Item ...$items): MutableListOf
    {
        return MutableListOf::of(Item::class, $items);
    }

    /**
     * @return MutableListOf<Tag>
     */
    protected function tags(Tag ...$tags): MutableListOf
    {
        return MutableListOf::of(Tag::class, $tags);
    }

    /**
     * @return MutableListOf<Item|null>
     */
    protected function nullableItems(?Item ...$items): MutableListOf
    {
        return MutableListOf::ofType(ElementType::ofClass(Item::class)->nullable(), $items);
    }
}
