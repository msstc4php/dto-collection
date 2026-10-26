<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Exception\IndexOutOfRange;
use MSSTC4PHP\DtoCollection\MutableListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\MutableItemList;

/**
 * @phpstan-type MutableItemsUnderTest MutableListOf<Item>|MutableItemList
 */
abstract class MutableContract extends ListContractCase
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
     * @return MutableItemsUnderTest
     */
    abstract protected function items(Item ...$items): object;

    public function testPushUnshiftSetInsert(): void
    {
        $list = $this->items($this->a);
        $list->push($this->b, $this->c);
        self::assertSame([$this->a, $this->b, $this->c], $list->toArray());

        $list->unshift($this->c, $this->b);
        self::assertSame([$this->c, $this->b, $this->a, $this->b, $this->c], $list->toArray());

        $list = $this->items($this->a);
        $list->set(0, $this->b);
        $list->set(1, $this->c);
        self::assertSame([$this->b, $this->c], $list->toArray());

        $list->insertAt(1, $this->a);
        $list->insertAt(0, $this->a);
        $list->insertAt(4, $this->a);
        self::assertSame([$this->a, $this->b, $this->a, $this->c, $this->a], $list->toArray());

        $list->push();
        $list->unshift();
        self::assertCount(5, $list);
    }

    public function testSetBeyondLengthThrows(): void
    {
        $list = $this->items($this->a);

        $this->expectException(IndexOutOfRange::class);
        $this->expectExceptionMessage('Index 2 is out of range for 1 element');
        $list->set(2, $this->b);
    }

    public function testInsertAtNegativeThrows(): void
    {
        $list = $this->items($this->a);

        $this->expectException(IndexOutOfRange::class);
        $list->insertAt(-1, $this->b);
    }

    public function testRemoveReindexes(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame($this->a, $list->remove(0));
        self::assertSame([$this->b, $this->c], $list->toArray());
        self::assertSame([0, 1], array_keys($list->toArray()));
        self::assertNull($list->remove(9));
        self::assertNull($list->remove(-1));
        self::assertSame($this->c, $list->remove(1));
        self::assertSame([$this->b], $list->toArray());
    }

    public function testRemoveElementFirstOccurrence(): void
    {
        $list = $this->items($this->a, $this->b, $this->a);

        self::assertTrue($list->removeElement(new Item('a', 0)));
        self::assertSame([$this->b, $this->a], $list->toArray());
        self::assertFalse($list->removeElement($this->c));
        self::assertSame([$this->b, $this->a], $list->toArray());
    }

    public function testRemoveIfReturnsCount(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame(2, $list->removeIf(static fn (Item $item): bool => $item->score !== 2));
        self::assertSame([$this->b], $list->toArray());
        self::assertSame(0, $list->removeIf(static fn (Item $item): bool => false));
        self::assertSame(1, $list->removeIf(static fn (Item $item, int $index): bool => $index === 0));
        self::assertSame([], $list->toArray());
    }

    public function testPopShift(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame($this->c, $list->pop());
        self::assertSame($this->a, $list->shift());
        self::assertSame([$this->b], $list->toArray());
        self::assertSame([0], array_keys($list->toArray()));
    }

    public function testPopShiftOnEmptyNull(): void
    {
        $list = $this->items();

        self::assertNull($list->pop());
        self::assertNull($list->shift());
    }

    public function testSortReverseSwapMove(): void
    {
        $list = $this->items($this->b, $this->c, $this->a);
        $list->sort(static fn (Item $left, Item $right): int => $left->score <=> $right->score);
        self::assertSame([$this->a, $this->b, $this->c], $list->toArray());

        $list->sortBy(static fn (Item $item): int => $item->score, descending: true);
        self::assertSame([$this->c, $this->b, $this->a], $list->toArray());

        $list->sortBy(static fn (Item $item): string => $item->id);
        self::assertSame([$this->a, $this->b, $this->c], $list->toArray());

        $list->reverse();
        self::assertSame([$this->c, $this->b, $this->a], $list->toArray());

        $list->swap(0, 2);
        self::assertSame([$this->a, $this->b, $this->c], $list->toArray());

        $list->move(0, 2);
        self::assertSame([$this->b, $this->c, $this->a], $list->toArray());

        $list->move(2, 0);
        self::assertSame([$this->a, $this->b, $this->c], $list->toArray());

        $list->move(1, 1);
        $list->swap(1, 1);
        self::assertSame([$this->a, $this->b, $this->c], $list->toArray());
    }

    public function testSwapOutOfRange(): void
    {
        $list = $this->items($this->a);

        $this->expectException(IndexOutOfRange::class);
        $list->swap(0, 9);
    }

    public function testSwapFirstIndexOutOfRange(): void
    {
        $list = $this->items($this->a);

        $this->expectException(IndexOutOfRange::class);
        $list->swap(-1, 0);
    }

    public function testMoveOutOfRange(): void
    {
        $list = $this->items($this->a, $this->b);

        foreach ([[0, 2], [2, 0], [-1, 0], [0, -1]] as [$from, $to]) {
            try {
                $list->move($from, $to);
                self::fail(sprintf('move(%d, %d) was accepted', $from, $to));
            } catch (IndexOutOfRange) {
                self::assertSame([$this->a, $this->b], $list->toArray());
            }
        }
    }

    public function testClear(): void
    {
        $list = $this->items($this->a, $this->b);
        $list->clear();

        self::assertTrue($list->isEmpty());
    }

    public function testQueriesReturnNewInstance(): void
    {
        $list = $this->items($this->a, $this->b);
        $filtered = $list->filter(static fn (Item $item): bool => $item->score === 1);
        $filtered->push($this->c);

        self::assertNotSame($list, $filtered);
        self::assertSame([$this->a, $this->b], $list->toArray());
        self::assertSame([$this->a, $this->c], $filtered->toArray());
    }

    public function testIterationIsSnapshot(): void
    {
        $list = $this->items($this->a, $this->b);
        $visits = 0;
        foreach ($list as $element) {
            $list->push($element);
            $visits++;
        }

        self::assertSame(2, $visits);
        self::assertCount(4, $list);
    }

    public function testToImmutableAndBack(): void
    {
        $list = $this->items($this->a, $this->b);
        $immutable = $list->toImmutable();
        $list->push($this->c);

        self::assertSame([$this->a, $this->b], $immutable->toArray());
        self::assertTrue($immutable->contains(new Item('a', 0)));

        $mutable = $immutable->toMutable();
        $mutable->push($this->c);
        self::assertSame([$this->a, $this->b], $immutable->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $mutable->toArray());
        self::assertTrue($list->toMutable()->contains(new Item('c', 0)));
    }
}
