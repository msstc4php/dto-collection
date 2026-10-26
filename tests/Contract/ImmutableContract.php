<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Exception\IndexOutOfRange;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\ItemList;

/**
 * @phpstan-type ImmutableItemsUnderTest ListOf<Item>|ItemList
 */
abstract class ImmutableContract extends ListContractCase
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

    /**
     * @return ImmutableItemsUnderTest
     */
    abstract protected function items(Item ...$items): object;

    public function testWithAppendsAndLeavesOriginal(): void
    {
        $list = $this->items($this->a);
        $more = $list->with($this->b, $this->c);

        self::assertCount(1, $list);
        self::assertSame([$this->a, $this->b, $this->c], $more->toArray());
        self::assertSame($list::class, $more::class);
        self::assertSame([$this->a], $list->with()->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $this->items($this->a, $this->b)->with($this->c)->toArray());
        self::assertSame([$this->c, $this->a, $this->b], $this->items($this->a, $this->b)->withPrepended($this->c)->toArray());
    }

    public function testWithPrepended(): void
    {
        self::assertSame([$this->b, $this->c, $this->a], $this->items($this->a)->withPrepended($this->b, $this->c)->toArray());
    }

    public function testWithSetBounds(): void
    {
        $list = $this->items($this->a);

        self::assertSame([$this->b], $list->withSet(0, $this->b)->toArray());
        self::assertSame([$this->a, $this->b], $list->withSet(1, $this->b)->toArray());
        self::assertSame([$this->c, $this->b], $this->items($this->a, $this->b)->withSet(0, $this->c)->toArray());
        self::assertSame([$this->a], $list->toArray());
    }

    public function testWithSetOutOfRange(): void
    {
        $this->expectException(IndexOutOfRange::class);
        $this->expectExceptionMessage('Index 5 is out of range for 1 element');

        $list = $this->items($this->a)->withSet(5, $this->b);
    }

    public function testWithSetNegative(): void
    {
        $this->expectException(IndexOutOfRange::class);

        $list = $this->items($this->a)->withSet(-1, $this->b);
    }

    public function testWithInsertedAt(): void
    {
        $list = $this->items($this->a, $this->c);

        self::assertSame([$this->b, $this->a, $this->c], $list->withInsertedAt(0, $this->b)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->withInsertedAt(1, $this->b)->toArray());
        self::assertSame([$this->a, $this->c, $this->b], $list->withInsertedAt(2, $this->b)->toArray());
    }

    public function testWithInsertedAtOutOfRange(): void
    {
        $this->expectException(IndexOutOfRange::class);

        $list = $this->items($this->a)->withInsertedAt(2, $this->b);
    }

    public function testWithoutIndexes(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame([$this->b], $list->without(0, 2)->toArray());
        self::assertSame([$this->a, $this->c], $list->without(1, 1)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->without()->toArray());
    }

    public function testWithoutOutOfRange(): void
    {
        $this->expectException(IndexOutOfRange::class);
        $this->expectExceptionMessage('Index 7 is out of range for 3 elements');

        $list = $this->items($this->a, $this->b, $this->c)->without(0, 7);
    }

    public function testWithoutElementRemovesFirstOccurrence(): void
    {
        $list = $this->items($this->a, $this->b, $this->a);

        self::assertSame([$this->b, $this->a], $list->withoutElement(new Item('a', 0))->toArray());
        self::assertSame([$this->b], $list->withoutElement($this->a, $this->a)->toArray());
        self::assertSame([$this->a, $this->b, $this->a], $list->withoutElement($this->c)->toArray());
    }

    public function testWithReplacedAll(): void
    {
        self::assertSame([$this->c, $this->b, $this->c], $this->items($this->a, $this->b, $this->a)->withReplaced(new Item('a', 0), $this->c)->toArray());
        self::assertSame([$this->a], $this->items($this->a)->withReplaced($this->b, $this->c)->toArray());
    }

    public function testWithoutIfReindexes(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame([$this->a, $this->c], $list->withoutIf(static fn (Item $item): bool => $item->score === 2)->toArray());
        self::assertSame([$this->b, $this->c], $list->withoutIf(static fn (Item $item, int $index): bool => $index === 0)->toArray());
    }
}
