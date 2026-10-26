<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Exception\EmptyCollection;
use MSSTC4PHP\DtoCollection\Exception\IndexOutOfRange;
use MSSTC4PHP\DtoCollection\Exception\NotExactlyOneElement;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

abstract class SearchingContract extends ListContractCase
{
    private Item $a;

    private Item $b;

    private Item $c;

    protected function setUp(): void
    {
        $this->a = new Item('a', 1);
        $this->b = new Item('b', 2);
        $this->c = new Item('c', 3);
    }

    public function testFirstLastOnEmptyAreNull(): void
    {
        self::assertNull($this->items()->first());
        self::assertNull($this->items()->last());
        self::assertSame($this->a, $this->items($this->a, $this->b)->first());
        self::assertSame($this->b, $this->items($this->a, $this->b)->last());
    }

    public function testFirstOrFailOnEmptyThrows(): void
    {
        $this->expectException(EmptyCollection::class);

        $first = $this->items()->firstOrFail();
    }

    public function testLastOrFailOnEmptyThrows(): void
    {
        $this->expectException(EmptyCollection::class);

        $last = $this->items()->lastOrFail();
    }

    public function testOrFailReturnsTheElement(): void
    {
        self::assertSame($this->a, $this->items($this->a, $this->b)->firstOrFail());
        self::assertSame($this->b, $this->items($this->a, $this->b)->lastOrFail());
    }

    public function testFirstOnNullableIsAmbiguousButOrFailIsNot(): void
    {
        self::assertNull($this->nullableItems(null, $this->a)->first());
        self::assertNull($this->nullableItems(null, $this->a)->firstOrFail());
        self::assertNull($this->nullableItems($this->a, null)->lastOrFail());
        self::assertNull($this->nullableItems($this->a, null)->getOrFail(1));
    }

    public function testGetAndAt(): void
    {
        $list = $this->items($this->a, $this->b);

        self::assertSame($this->a, $list->get(0));
        self::assertSame($this->b, $list->get(1));
        self::assertNull($list->get(2));
        self::assertNull($list->get(-1));
        self::assertSame($this->b, $list->getOrFail(1));
        self::assertSame($this->a, $list->at(0));
        self::assertSame($this->b, $list->at(-1));
        self::assertSame($this->a, $list->at(-2));
        self::assertNull($list->at(-3));
        self::assertNull($list->at(2));
        self::assertNull($list->at(9));
    }

    public function testGetOrFailOutOfRange(): void
    {
        $this->expectException(IndexOutOfRange::class);
        $this->expectExceptionMessage('Index 9 is out of range for 2 elements');

        $element = $this->items($this->a, $this->b)->getOrFail(9);
    }

    public function testGetOrFailNegative(): void
    {
        $this->expectException(IndexOutOfRange::class);

        $element = $this->items($this->a)->getOrFail(-1);
    }

    public function testSingle(): void
    {
        self::assertSame($this->a, $this->items($this->a)->single());
        self::assertSame($this->a, $this->items($this->a)->singleOrNull());
        self::assertNull($this->items()->singleOrNull());
        self::assertNull($this->items($this->a, $this->b)->singleOrNull());
    }

    public function testSingleOnEmptyThrows(): void
    {
        $this->expectException(NotExactlyOneElement::class);
        $this->expectExceptionMessage('Expected exactly one element, got 0');

        $element = $this->items()->single();
    }

    public function testSingleOnTwoThrows(): void
    {
        $this->expectException(NotExactlyOneElement::class);
        $this->expectExceptionMessage('Expected exactly one element, got 2');

        $element = $this->items($this->a, $this->b)->single();
    }

    public function testContainsUsesEquality(): void
    {
        $tag = new Tag('t');

        self::assertTrue($this->items(new Item('a', 1))->contains(new Item('a', 5)));
        self::assertFalse($this->items(new Item('a', 1))->contains(new Item('b', 1)));
        self::assertFalse($this->items()->contains(new Item('a', 1)));
        self::assertFalse($this->tags($tag)->contains(new Tag('t')));
        self::assertTrue($this->tags($tag)->contains($tag));
    }

    public function testContainsAllAny(): void
    {
        $list = $this->items($this->a, $this->b);

        self::assertTrue($list->containsAll());
        self::assertFalse($list->containsAny());
        self::assertTrue($list->containsAll(new Item('a', 0), new Item('b', 0)));
        self::assertFalse($list->containsAll(new Item('a', 0), new Item('z', 0)));
        self::assertTrue($list->containsAny(new Item('z', 0), new Item('b', 0)));
        self::assertFalse($list->containsAny(new Item('z', 0), new Item('y', 0)));
    }

    public function testPositionOf(): void
    {
        $list = $this->items($this->a, $this->b, new Item('a', 7));

        self::assertSame(0, $list->positionOf(new Item('a', 0)));
        self::assertSame(1, $list->positionOf($this->b));
        self::assertNull($list->positionOf($this->c));
    }

    public function testFindAndFindLastAndFindPosition(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame($this->b, $list->find(static fn (Item $item, int $index): bool => $index === 1));
        self::assertSame($this->b, $list->find(static fn (Item $item): bool => $item->score >= 2));
        self::assertSame($this->c, $list->findLast(static fn (Item $item): bool => $item->score >= 2));
        self::assertSame($this->a, $list->findLast(static fn (Item $item, int $index): bool => $index === 0));
        self::assertNull($list->find(static fn (Item $item): bool => false));
        self::assertNull($list->findLast(static fn (Item $item): bool => false));
        self::assertSame(1, $list->findPosition(static fn (Item $item): bool => $item->score >= 2));
        self::assertSame(2, $list->findPosition(static fn (Item $item, int $index): bool => $index === 2));
        self::assertNull($list->findPosition(static fn (Item $item): bool => false));
    }

    public function testAnyAllNone(): void
    {
        $list = $this->items($this->a, $this->b);

        self::assertTrue($this->items()->all(static fn (Item $item): bool => false));
        self::assertFalse($this->items()->any(static fn (Item $item): bool => true));
        self::assertTrue($this->items()->none(static fn (Item $item): bool => true));
        self::assertTrue($list->any(static fn (Item $item): bool => $item->score === 2));
        self::assertFalse($list->any(static fn (Item $item): bool => $item->score === 3));
        self::assertTrue($list->all(static fn (Item $item): bool => $item->score > 0));
        self::assertFalse($list->all(static fn (Item $item): bool => $item->score > 1));
        self::assertTrue($list->none(static fn (Item $item): bool => $item->score > 2));
        self::assertFalse($list->none(static fn (Item $item): bool => $item->score > 1));
        self::assertTrue($list->any(static fn (Item $item, int $index): bool => $index === 1));
        self::assertFalse($list->all(static fn (Item $item, int $index): bool => $index === 0));
        self::assertFalse($list->none(static fn (Item $item, int $index): bool => $index === 1));
    }

    public function testRandomReturnsMember(): void
    {
        $list = $this->items($this->a, $this->b);
        $seen = [];
        for ($attempt = 0; $attempt < 200; $attempt++) {
            $element = $list->random();
            self::assertContains($element, [$this->a, $this->b]);
            $seen[spl_object_id($element)] = true;
        }

        self::assertCount(2, $seen);
        self::assertNull($this->items()->random());
    }
}
