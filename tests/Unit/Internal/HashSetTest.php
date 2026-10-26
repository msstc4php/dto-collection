<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Internal;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\EqualityResolver;
use MSSTC4PHP\DtoCollection\Equality\StrictEquality;
use MSSTC4PHP\DtoCollection\Internal\HashSet;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use PHPUnit\Framework\TestCase;

final class HashSetTest extends TestCase
{
    public function testHashedScalars(): void
    {
        $set = new HashSet(new StrictEquality(), [1, 2, '3', 0.0]);

        self::assertTrue($set->contains(1));
        self::assertTrue($set->contains('3'));
        self::assertFalse($set->contains(3));
        self::assertTrue($set->contains(-0.0));
        self::assertFalse($set->contains(null));
    }

    public function testNanNeverContained(): void
    {
        $set = new HashSet(new StrictEquality(), [NAN, 1.0]);

        self::assertFalse($set->contains(NAN));
        self::assertTrue($set->contains(1.0));
    }

    public function testPairwiseForEquatables(): void
    {
        $set = new HashSet(EqualityResolver::forType(ElementType::ofClass(Item::class)), [new Item('a', 1)]);

        self::assertTrue($set->contains(new Item('a', 7)));
        self::assertFalse($set->contains(new Item('b', 1)));
    }

    public function testMixedSetOfItemsAndInts(): void
    {
        $set = new HashSet(EqualityResolver::forType(ElementType::parse(Item::class . '|int')), [new Item('a', 1), 5]);

        self::assertTrue($set->contains(5));
        self::assertTrue($set->contains(new Item('a', 2)));
        self::assertFalse($set->contains(6));
        self::assertFalse($set->contains(new Item('z', 5)));
    }

    public function testAddGrowsTheSet(): void
    {
        $set = new HashSet(new StrictEquality(), []);
        $set->add(1);
        $set->add(new Item('a', 1));

        self::assertTrue($set->contains(1));
        self::assertFalse($set->contains(new Item('a', 1)));
    }

    public function testAcceptsGenerators(): void
    {
        $set = new HashSet(new StrictEquality(), (static function () {
            yield 'k' => 'v';
        })());

        self::assertTrue($set->contains('v'));
    }
}
