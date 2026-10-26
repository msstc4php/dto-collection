<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Doctrine;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\MutableListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\OdmItemCollection;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

/**
 * Contract hooks for the ODM variant; the Tag and nullable cases have no fixture class and run on MutableListOf.
 */
trait OdmItemCollectionLists
{
    protected function items(Item ...$items): OdmItemCollection
    {
        return OdmItemCollection::fromArray($items);
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
