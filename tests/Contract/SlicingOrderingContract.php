<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;

abstract class SlicingOrderingContract extends ListContractCase
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

    public function testFilterReindexes(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame([$this->b, $this->c], $list->filter(fn (Item $item): bool => $item !== $this->a)->toArray());
        self::assertSame([$this->a, $this->c], $list->filter(static fn (Item $item, int $index): bool => $index !== 1)->toArray());
        self::assertSame([$this->b], $list->reject(fn (Item $item): bool => $item !== $this->b)->toArray());
        self::assertSame([$this->a, $this->c], $list->reject(static fn (Item $item, int $index): bool => $index === 1)->toArray());
    }

    public function testQueriesDoNotTouchOriginal(): void
    {
        $list = $this->items($this->a, $this->b);
        $filtered = $list->filter(static fn (Item $item): bool => false);
        $sorted = $list->sortedBy(static fn (Item $item): int => -$item->score);

        self::assertCount(2, $list);
        self::assertSame([$this->a, $this->b], $list->toArray());
        self::assertNotSame($list, $list->filter(static fn (Item $item): bool => true));
    }

    public function testReturnsSameClass(): void
    {
        $list = $this->items($this->a);

        self::assertSame($list::class, $list->filter(static fn (Item $item): bool => true)::class);
        self::assertSame($list::class, $list->sortedBy(static fn (Item $item): int => $item->score)::class);
        self::assertSame($list::class, $list->sliced(0)::class);
    }

    public function testTransform(): void
    {
        $list = $this->items($this->a, $this->b);

        self::assertEquals(
            [new Item('a', 10), new Item('b', 21)],
            $list->transform(static fn (Item $item, int $index): Item => new Item($item->id, $item->score * 10 + $index))->toArray(),
        );
    }

    public function testTransformRecoerces(): void
    {
        $this->expectException(ElementTypeMismatch::class);

        // Reason: a mapper returning another type is the input under test.
        $list = $this->items($this->a)->transform(static fn (Item $item): string => 'x'); // @phpstan-ignore argument.type
    }

    public function testSliced(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame([$this->b, $this->c], $list->sliced(-2)->toArray());
        self::assertSame([$this->b], $list->sliced(1, 1)->toArray());
        self::assertSame([$this->a, $this->b], $list->sliced(0, -1)->toArray());
        self::assertSame([], $list->sliced(5)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->sliced(0)->toArray());
    }

    public function testTakeDropBounds(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame([], $list->take(0)->toArray());
        self::assertSame([$this->a, $this->b], $list->take(2)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->take(9)->toArray());
        self::assertSame([], $list->takeLast(0)->toArray());
        self::assertSame([$this->b, $this->c], $list->takeLast(2)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->takeLast(9)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->drop(0)->toArray());
        self::assertSame([$this->c], $list->drop(2)->toArray());
        self::assertSame([], $list->drop(9)->toArray());
        self::assertSame([$this->a, $this->b, $this->c], $list->dropLast(0)->toArray());
        self::assertSame([$this->a, $this->b], $list->dropLast(1)->toArray());
        self::assertSame([], $list->dropLast(9)->toArray());
    }

    public function testNegativeCountsAreRejected(): void
    {
        $list = $this->items($this->a);
        $operations = [
            'take' => static fn (): object => $list->take(-1),
            'takeLast' => static fn (): object => $list->takeLast(-1),
            'drop' => static fn (): object => $list->drop(-1),
            'dropLast' => static fn (): object => $list->dropLast(-1),
        ];
        foreach ($operations as $method => $operation) {
            try {
                $result = $operation();
                self::fail($method . '(-1) was accepted');
            } catch (InvalidArgument $exception) {
                self::assertSame($method . '() needs a count of 0 or more, -1 given', $exception->getMessage());
            }
        }
    }

    public function testTakeWhileDropWhile(): void
    {
        $list = $this->items($this->a, $this->b, $this->c, new Item('d', 1));

        self::assertSame([$this->a, $this->b], $list->takeWhile(static fn (Item $item): bool => $item->score < 3)->toArray());
        self::assertSame([$this->c], $list->dropWhile(static fn (Item $item): bool => $item->score < 3)->takeWhile(static fn (Item $item): bool => $item->score === 3)->toArray());
        self::assertSame([$this->a], $list->takeWhile(static fn (Item $item, int $index): bool => $index < 1)->toArray());
        self::assertSame([], $list->takeWhile(static fn (Item $item): bool => false)->toArray());
        self::assertCount(4, $list->takeWhile(static fn (Item $item): bool => true));
        self::assertCount(4, $list->dropWhile(static fn (Item $item): bool => false));
        self::assertCount(0, $list->dropWhile(static fn (Item $item): bool => true));
        self::assertCount(2, $list->dropWhile(static fn (Item $item, int $index): bool => $index < 2));
    }

    public function testSorted(): void
    {
        $list = $this->items($this->b, $this->c, $this->a);

        self::assertSame([$this->a, $this->b, $this->c], $list->sorted(static fn (Item $left, Item $right): int => $left->score <=> $right->score)->toArray());
    }

    public function testSortedByIsStableAndDescending(): void
    {
        $first = new Item('x', 1);
        $second = new Item('y', 1);
        $list = $this->items($this->b, $first, $this->c, $second);

        self::assertSame([$first, $second, $this->b, $this->c], $list->sortedBy(static fn (Item $item): int => $item->score)->toArray());
        self::assertSame([$this->c, $this->b, $first, $second], $list->sortedBy(static fn (Item $item): int => $item->score, descending: true)->toArray());
    }

    public function testMultiKeyByChaining(): void
    {
        $a2 = new Item('a', 2);
        $b1 = new Item('b', 1);
        $a1 = new Item('a', 1);
        $list = $this->items($a2, $b1, $a1);

        self::assertSame(
            [$a1, $b1, $a2],
            $list->sortedBy(static fn (Item $item): string => $item->id)->sortedBy(static fn (Item $item): int => $item->score)->toArray(),
        );
        self::assertSame([$a1, $b1, $a2], $list->sortedBy(static fn (Item $item): array => [$item->score, $item->id])->toArray());
    }

    public function testReversedShuffledKeepMembers(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);

        self::assertSame([$this->c, $this->b, $this->a], $list->reversed()->toArray());
        $orders = [];
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $shuffled = $list->shuffled()->toArray();
            $ids = array_map(static fn (Item $item): string => $item->id, $shuffled);
            $orders[implode('', $ids)] = true;
            sort($ids);
            self::assertSame(['a', 'b', 'c'], $ids);
            self::assertSame([0, 1, 2], array_keys($shuffled));
        }
        self::assertGreaterThan(1, count($orders));
    }
}
