<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Immutable;

use ArrayIterator;
use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\IntegerList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\ItemList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;
use PHPUnit\Framework\TestCase;
use TypeError;

final class ItemListTest extends TestCase
{
    public function testNativeTypeRejectsWrongType(): void
    {
        $this->expectException(TypeError::class);

        // Reason: the wrong element type is the input under test.
        $list = ItemList::of(new Tag('x')); // @phpstan-ignore argument.type
    }

    public function testFromArrayRuntimeChecked(): void
    {
        $this->expectException(ElementTypeMismatch::class);

        // Reason: the wrong element type is the input under test.
        $list = ItemList::fromArray([new Tag('x')]); // @phpstan-ignore argument.type
    }

    public function testFactories(): void
    {
        $item = new Item('a', 1);

        self::assertSame([$item], ItemList::of($item)->toArray());
        self::assertSame([$item], ItemList::fromArray(['k' => $item])->toArray());
        self::assertSame([$item], ItemList::fromIterable(new ArrayIterator([$item]))->toArray());
        self::assertSame([], ItemList::empty()->toArray());
        self::assertSame([$item], (new ItemList([$item]))->toArray());
    }

    public function testIntegerListNumeric(): void
    {
        self::assertSame(3, IntegerList::of(1, 2)->sum());
        self::assertSame(1, IntegerList::of(1, 2)->first());
        self::assertTrue(IntegerList::of(1, 2)->contains(2));
    }

    public function testMapIntoTypedList(): void
    {
        $item = new Item('a', 1);
        $mapped = ListOf::of(Item::class, [$item])->mapInto(ItemList::class, static fn (Item $element): Item => $element);

        self::assertInstanceOf(ItemList::class, $mapped);
        self::assertSame([$item], $mapped->toArray());
        self::assertSame([2], ItemList::of($item)->mapInto(IntegerList::class, static fn (Item $element): int => $element->score + 1)->toArray());
    }
}
