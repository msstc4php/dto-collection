<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Exception\ReadOnlyCollection;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;

/**
 * Every variant except ODM, whose Doctrine interface requires writable offsets.
 *
 * @phpstan-require-extends ListContractCase
 */
trait ReadOnlyOffsetsContract
{
    public function testOffsetSetIsReadOnly(): void
    {
        $list = $this->items(new Item('a', 1));

        $this->expectException(ReadOnlyCollection::class);
        $list[0] = new Item('b', 2);
    }

    public function testOffsetUnsetIsReadOnly(): void
    {
        $list = $this->items(new Item('a', 1));

        $this->expectException(ReadOnlyCollection::class);
        unset($list[0]);
    }
}
