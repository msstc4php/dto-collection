<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Contract;

use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;

abstract class SetOperationsContract extends ListContractCase
{
    public function testUniqueByEquatable(): void
    {
        $a1 = new Item('a', 1);
        $b1 = new Item('b', 1);

        self::assertSame([$a1, $b1], $this->items($a1, new Item('a', 2), $b1, new Item('b', 9))->unique()->toArray());
        self::assertSame([], $this->items()->unique()->toArray());
    }

    public function testUniqueByIdentityForTags(): void
    {
        $tag = new Tag('x');
        $twin = new Tag('x');

        self::assertSame([$tag, $twin], $this->tags($tag, $twin, $tag)->unique()->toArray());
    }

    public function testUniqueBy(): void
    {
        $a1 = new Item('a', 1);
        $b1 = new Item('b', 1);
        $c2 = new Item('c', 2);

        self::assertSame([$a1, $c2], $this->items($a1, $b1, $c2)->uniqueBy(static fn (Item $item): int => $item->score)->toArray());
        self::assertSame(
            ['x'],
            array_map(static fn (Tag $tag): string => $tag->name, $this->tags(new Tag('x'), new Tag('x'))->uniqueBy(static fn (Tag $tag): string => $tag->name)->toArray()),
        );
    }

    public function testDiffKeepsOwnDuplicates(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);
        $c = new Item('c', 3);

        self::assertSame([$a, $a], $this->items($a, $a, $b, $c)->diff([new Item('b', 0)], [new Item('c', 0)])->toArray());
        self::assertSame([$a, $b], $this->items($a, $b)->diff()->toArray());
        self::assertSame([], $this->items($a)->diff([new Item('a', 5)])->toArray());
        self::assertSame([$b], $this->items($a, $b)->diff([$a])->toArray());
    }

    public function testDiffBy(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);

        self::assertSame([$b], $this->items($a, $b)->diffBy(static fn (Item $item): int => $item->score, [new Item('z', 1)])->toArray());
        self::assertSame([$a, $b], $this->items($a, $b)->diffBy(static fn (Item $item): int => $item->score)->toArray());
        self::assertSame([], $this->items($a, $b)->diffBy(static fn (Item $item): int => $item->score, [new Item('z', 1), new Item('y', 2)])->toArray());
    }

    public function testIntersectAll(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);
        $c = new Item('c', 3);

        self::assertSame([$c], $this->items($a, $b, $c)->intersect([new Item('b', 0), new Item('c', 0)], [new Item('c', 0)])->toArray());
        self::assertSame([$a, $b], $this->items($a, $b)->intersect()->toArray());
        self::assertSame([], $this->items($a, $b)->intersect([])->toArray());
        self::assertSame([$a, $a], $this->items($a, $a)->intersect([$a])->toArray());
    }

    public function testIntersectBy(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);

        self::assertSame([$a], $this->items($a, $b)->intersectBy(static fn (Item $item): int => $item->score, [new Item('z', 1)], [new Item('y', 1)])->toArray());
        self::assertSame([], $this->items($a, $b)->intersectBy(static fn (Item $item): int => $item->score, [new Item('z', 1)], [new Item('y', 2)])->toArray());
        self::assertSame([$a, $b], $this->items($a, $b)->intersectBy(static fn (Item $item): int => $item->score)->toArray());
        self::assertSame([$b], $this->items($a, $b)->intersectBy(static fn (Item $item): int => $item->score, [new Item('z', 2)])->toArray());
    }

    public function testUnionAndMerge(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);
        $c = new Item('c', 3);

        self::assertSame([$a, $b, $c], $this->items($a, $b)->union([new Item('b', 0), $c], [$c])->toArray());
        self::assertSame([$a], $this->items($a, new Item('a', 2))->union()->toArray());
        self::assertSame([$a, $b, $b, $c], $this->items($a, $b)->merge([$b], [$c])->toArray());
        self::assertSame([$a], $this->items($a)->merge()->toArray());
    }

    public function testAcceptsAnotherList(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 2);

        self::assertSame([$a], $this->items($a, $b)->diff($this->items(new Item('b', 0)))->toArray());
        self::assertSame([$a, $b, $a], $this->items($a, $b)->merge($this->items($a))->toArray());
    }
}
