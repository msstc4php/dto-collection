<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use Countable;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;

abstract class StructuringMappingContract extends ListContractCase
{
    private Item $a;

    private Item $b;

    private Item $c;

    private Item $d;

    protected function setUp(): void
    {
        $this->a = new Item('a', 1);
        $this->b = new Item('b', 2);
        $this->c = new Item('c', 3);
        $this->d = new Item('d', 4);
    }

    public function testSplit(): void
    {
        [$matching, $rest] = $this->items($this->a, $this->b, $this->c)->split(static fn (Item $item): bool => $item->score !== 2);

        self::assertSame([$this->a, $this->c], $matching->toArray());
        self::assertSame([$this->b], $rest->toArray());

        [$first, $others] = $this->items($this->a, $this->b)->split(static fn (Item $item, int $index): bool => $index === 0);
        self::assertSame([$this->a], $first->toArray());
        self::assertSame([$this->b], $others->toArray());
        self::assertSame($first::class, $this->items()::class);
    }

    public function testGroupByPreservesOrderWithinGroups(): void
    {
        $groups = $this->items($this->a, $this->b, $this->c, $this->d)->groupBy(static fn (Item $item): string => $item->score % 2 === 0 ? 'even' : 'odd');

        self::assertSame(['odd', 'even'], array_keys($groups));
        self::assertSame([$this->a, $this->c], $groups['odd']->toArray());
        self::assertSame([$this->b, $this->d], $groups['even']->toArray());
        self::assertSame([], $this->items()->groupBy(static fn (Item $item): string => $item->id));
    }

    public function testChunkLastPartial(): void
    {
        $chunks = $this->items($this->a, $this->b, $this->c)->chunk(2);

        self::assertCount(2, $chunks);
        self::assertSame([$this->a, $this->b], $chunks[0]->toArray());
        self::assertSame([$this->c], $chunks[1]->toArray());
        self::assertSame([], $this->items()->chunk(3));
        self::assertCount(1, $this->items($this->a)->chunk(5));
    }

    public function testChunkZero(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('chunk() needs a size of 1 or more, 0 given');

        $chunks = $this->items($this->a)->chunk(0);
    }

    public function testWindowed(): void
    {
        $list = $this->items($this->a, $this->b, $this->c, $this->d);

        self::assertSame(
            [[$this->a, $this->b], [$this->b, $this->c], [$this->c, $this->d]],
            array_map(static fn (object $window): array => $window->toArray(), $list->windowed(2)),
        );
        self::assertSame([[$this->a, $this->b, $this->c]], array_map(static fn (object $window): array => $window->toArray(), $list->windowed(3, 2)));
        self::assertSame(
            [[$this->a, $this->b, $this->c], [$this->c, $this->d]],
            array_map(static fn (object $window): array => $window->toArray(), $list->windowed(3, 2, partial: true)),
        );
        self::assertSame([[$this->a], [$this->d]], array_map(static fn (object $window): array => $window->toArray(), $list->windowed(1, 3)));
        self::assertSame([], $list->windowed(5));
        self::assertSame(
            [[$this->a, $this->b, $this->c, $this->d], [$this->b, $this->c, $this->d], [$this->c, $this->d], [$this->d]],
            array_map(static fn (object $window): array => $window->toArray(), $list->windowed(5, partial: true)),
        );
        self::assertSame([], $this->items()->windowed(1, partial: true));
    }

    public function testWindowedRejectsBadSizes(): void
    {
        foreach ([[0, 1], [1, 0], [-1, 1]] as [$size, $step]) {
            try {
                $windows = $this->items($this->a)->windowed($size, $step);
                self::fail('windowed accepted a size or step below 1');
            } catch (InvalidArgument $exception) {
                self::assertStringStartsWith('windowed() needs', $exception->getMessage());
            }
        }
    }

    public function testChunkWhile(): void
    {
        $e = new Item('e', 6);
        $list = $this->items($this->a, $this->b, $this->d, $e);

        self::assertSame(
            [[$this->a, $this->b], [$this->d]],
            array_map(static fn (object $chunk): array => $chunk->toArray(), $this->items($this->a, $this->b, $this->d)->chunkWhile(static fn (Item $previous, Item $current): bool => $current->score === $previous->score + 1)),
        );
        self::assertCount(3, $list->chunkWhile(static fn (Item $previous, Item $current): bool => $current->score - $previous->score <= 1));
        self::assertSame([], $this->items()->chunkWhile(static fn (Item $previous, Item $current): bool => true));
    }

    public function testKeyByLastWins(): void
    {
        $other = new Item('a', 9);

        self::assertSame(['a' => $other, 'b' => $this->b], $this->items($this->a, $this->b, $other)->keyBy(static fn (Item $item): string => $item->id));
    }

    public function testMapToChecksTargetType(): void
    {
        $scores = $this->items($this->a, $this->b)->mapTo('int', static fn (Item $item): int => $item->score);

        self::assertInstanceOf(ListOf::class, $scores);
        self::assertSame([1, 2], $scores->toArray());
        self::assertSame([0, 1], $this->items($this->a, $this->b)->mapTo(ElementType::integer(), static fn (Item $item, int $index): int => $index)->toArray());
        self::assertSame([1.0], $this->items($this->a)->mapTo(ElementType::float(), static fn (Item $item): int => $item->score)->toArray());
    }

    public function testMapToRejectsWrongType(): void
    {
        $this->expectException(ElementTypeMismatch::class);

        $list = $this->items($this->a)->mapTo('int', static fn (Item $item): string => 'x');
    }

    public function testFlatMapTo(): void
    {
        $letters = $this->items($this->a, $this->b)->flatMapTo('string', static fn (Item $item, int $index): iterable => array_fill(0, $item->score, $item->id . $index));

        self::assertSame(['a0', 'b1', 'b1'], $letters->toArray());
        self::assertSame([], $this->items($this->a)->flatMapTo(ElementType::string(), static fn (Item $item): array => [])->toArray());
    }

    public function testMapToArrayUnchecked(): void
    {
        self::assertSame([['a', 0], ['b', 1]], $this->items($this->a, $this->b)->mapToArray(static fn (Item $item, int $index): array => [$item->id, $index]));
        self::assertSame(['a'], $this->items($this->a)->mapToArray(static fn (Item $item): string => $item->id));
    }

    public function testEachStopsOnFalse(): void
    {
        $list = $this->items($this->a, $this->b, $this->c);
        $visited = [];

        self::assertSame($list, $list->each(static function (Item $item, int $index) use (&$visited): ?bool {
            $visited[] = $index;

            return $index === 1 ? false : null;
        }));
        self::assertSame([0, 1], $visited);

        $visits = 0;
        $list->each(static function (Item $item) use (&$visits): int {
            $visits++;

            return 0;
        });
        self::assertSame(3, $visits);
    }

    public function testPipeChains(): void
    {
        $list = $this->items($this->a, $this->b);

        self::assertSame(20, $list->pipe(static fn (Countable $items): int => count($items), static fn (int $count): int => $count * 10));
        self::assertSame($list, $list->pipe());
    }
}
