<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\MutableListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\ItemList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\MutableItemList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\OdmItemCollection;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Base of the contract suites: each concrete subclass builds the lists of one variant.
 *
 * @phpstan-type ItemsUnderTest ListOf<Item>|ItemList|MutableListOf<Item>|MutableItemList|OdmItemCollection
 * @phpstan-type TagsUnderTest ListOf<Tag>|MutableListOf<Tag>
 * @phpstan-type NullableItemsUnderTest ListOf<Item|null>|MutableListOf<Item|null>
 */
abstract class ListContractCase extends TestCase
{
    /**
     * @return ItemsUnderTest
     */
    abstract protected function items(Item ...$items): object;

    /**
     * @return TagsUnderTest
     */
    abstract protected function tags(Tag ...$tags): object;

    /**
     * @return NullableItemsUnderTest
     */
    abstract protected function nullableItems(?Item ...$items): object;
}
