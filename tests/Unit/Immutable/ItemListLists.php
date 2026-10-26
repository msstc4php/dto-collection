<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Immutable;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\ItemList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

/**
 * Contract hooks for ItemList; the Tag and nullable cases have no fixture class and run on ListOf.
 */
trait ItemListLists
{
    protected function items(Item ...$items): ItemList
    {
        return ItemList::fromArray($items);
    }

    /**
     * @return ListOf<Tag>
     */
    protected function tags(Tag ...$tags): ListOf
    {
        return ListOf::of(Tag::class, $tags);
    }

    /**
     * @return ListOf<Item|null>
     */
    protected function nullableItems(?Item ...$items): ListOf
    {
        return ListOf::ofType(ElementType::ofClass(Item::class)->nullable(), $items);
    }
}
