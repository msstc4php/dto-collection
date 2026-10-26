<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

abstract class ReadableListContract extends ListContractCase
{
    public function testCountAndEmptiness(): void
    {
        self::assertCount(0, $this->items());
        self::assertTrue($this->items()->isEmpty());
        self::assertFalse($this->items()->isNotEmpty());

        $list = $this->items(new Item('a', 1), new Item('b', 2));
        self::assertCount(2, $list);
        self::assertFalse($list->isEmpty());
        self::assertTrue($list->isNotEmpty());
    }

    public function testToArrayIsListAndDetached(): void
    {
        $a = new Item('a', 1);
        $list = $this->items($a);
        $array = $list->toArray();
        $array[] = new Item('b', 2);

        self::assertSame([$a], $list->toArray());
        self::assertCount(1, $list);
    }

    public function testIteratorIsIndexed(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);

        self::assertSame([0 => $a, 1 => $b], iterator_to_array($this->items($a, $b)));
    }

    public function testJsonSerialize(): void
    {
        self::assertSame('[{"id":"x","score":1}]', json_encode($this->items(new Item('x', 1)), JSON_THROW_ON_ERROR));
        self::assertSame('[]', json_encode($this->items(), JSON_THROW_ON_ERROR));
    }

    public function testOffsetRead(): void
    {
        $a = new Item('a', 1);
        $list = $this->items($a);

        self::assertTrue(isset($list[0]));
        self::assertFalse(isset($list[1]));
        self::assertFalse(isset($list[-1]));
        self::assertSame($a, $list[0]);
        self::assertNull($list[5]);
        self::assertNull($list[-1]);
    }

    public function testListEquals(): void
    {
        self::assertTrue($this->items(new Item('a', 1))->equals($this->items(new Item('a', 9))));
        self::assertFalse($this->items(new Item('a', 1))->equals($this->items(new Item('b', 1))));
        self::assertFalse($this->items(new Item('a', 1))->equals($this->items(new Item('a', 1), new Item('a', 1))));
        self::assertTrue($this->items()->equals($this->items()));
        self::assertFalse($this->items(new Item('a', 1), new Item('b', 1))->equals($this->items(new Item('b', 1), new Item('a', 1))));
    }

    public function testListEqualsByIdentityWithoutEquatable(): void
    {
        $tag = new Tag('x');

        self::assertFalse($this->tags(new Tag('x'))->equals($this->tags(new Tag('x'))));
        self::assertTrue($this->tags($tag)->equals($this->tags($tag)));
    }
}
