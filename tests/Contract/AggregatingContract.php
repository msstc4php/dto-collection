<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;

abstract class AggregatingContract extends ListContractCase
{
    public function testReduceWithIndex(): void
    {
        $list = $this->items(new Item('a', 1), new Item('b', 2));

        self::assertSame(1, $list->reduce(static fn (int $carry, Item $item, int $index): int => $carry + $index, 0));
        self::assertSame(3, $list->reduce(static fn (int $carry, Item $item): int => $carry + $item->score, 0));
        self::assertSame('ab', $list->reduce(static fn (string $carry, Item $item): string => $carry . $item->id, ''));
    }

    public function testReduceDefaultInitialNull(): void
    {
        self::assertNull($this->items()->reduce(static fn (mixed $carry, Item $item): mixed => $carry));
        self::assertSame(5, $this->items()->reduce(static fn (int $carry, Item $item): int => $carry + 1, 5));
    }

    public function testCountWhere(): void
    {
        $list = $this->items(new Item('a', 1), new Item('b', 2), new Item('c', 3));

        self::assertSame(2, $list->countWhere(static fn (Item $item): bool => $item->score > 1));
        self::assertSame(1, $list->countWhere(static fn (Item $item, int $index): bool => $index === 0));
        self::assertSame(0, $this->items()->countWhere(static fn (Item $item): bool => true));
    }

    public function testCountByKeepsFirstSeenOrder(): void
    {
        $list = $this->items(new Item('b', 1), new Item('a', 2), new Item('b', 3), new Item('c', 4), new Item('a', 5));

        self::assertSame(['b' => 2, 'a' => 2, 'c' => 1], $list->countBy(static fn (Item $item): string => $item->id));
        self::assertSame([1 => 3, 0 => 2], $list->countBy(static fn (Item $item): int => $item->score % 2));
        self::assertSame([], $this->items()->countBy(static fn (Item $item): string => $item->id));
    }

    public function testSumAndAverageBy(): void
    {
        $list = $this->items(new Item('a', 1), new Item('b', 2));

        self::assertSame(3, $list->sumBy(static fn (Item $item): int => $item->score));
        self::assertSame(1.5, $list->averageBy(static fn (Item $item): int => $item->score));
        self::assertSame(1.5, $list->sumBy(static fn (Item $item): float => $item->score / 2));
        self::assertSame(2.0, $this->items(new Item('a', 2))->averageBy(static fn (Item $item): int => $item->score));
        self::assertNull($this->items()->averageBy(static fn (Item $item): int => $item->score));
        self::assertSame(0, $this->items()->sumBy(static fn (Item $item): int => $item->score));
    }

    public function testMinMaxByFirstOfEqualsWins(): void
    {
        $first = new Item('a', 1);
        $second = new Item('b', 1);
        $high = new Item('c', 3);
        $alsoHigh = new Item('d', 3);
        $list = $this->items($high, $first, $second, $alsoHigh);

        self::assertSame($first, $list->minBy(static fn (Item $item): int => $item->score));
        self::assertSame($high, $list->maxBy(static fn (Item $item): int => $item->score));
        self::assertSame($second, $list->maxBy(static fn (Item $item): string => $item->id === 'b' ? 'z' : $item->id));
        self::assertNull($this->items()->minBy(static fn (Item $item): int => $item->score));
        self::assertNull($this->items()->maxBy(static fn (Item $item): int => $item->score));
    }
}
