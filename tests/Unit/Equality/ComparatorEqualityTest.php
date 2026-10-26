<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Equality;

use ArrayObject;
use Countable;
use MSSTC4PHP\DtoCollection\Equality\ComparatorEquality;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Cat;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Dog;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use PHPUnit\Framework\TestCase;

final class ComparatorEqualityTest extends TestCase
{
    public function testComparatorUsesClosureForItsClass(): void
    {
        $equality = ComparatorEquality::of(Item::class, static fn (Item $left, Item $right): bool => $left->equals($right));

        self::assertTrue($equality->equals(new Item('a', 1), new Item('a', 9)));
        self::assertFalse($equality->equals(new Item('a', 1), new Item('b', 1)));
        self::assertNull($equality->hashKey(new Item('a', 1)));
    }

    public function testComparatorDifferentBranchesNeverEqual(): void
    {
        $equality = ComparatorEquality::of(Cat::class, static fn (Cat $left, Cat $right): bool => true);

        self::assertFalse($equality->equals(new Cat('x'), new Dog('x')));
        self::assertFalse($equality->equals(new Dog('x'), new Cat('x')));
    }

    public function testComparatorFallsBackToStrictForOtherValues(): void
    {
        $equality = ComparatorEquality::of(Cat::class, static fn (Cat $left, Cat $right): bool => $left->equals($right));
        $dog = new Dog('x');

        self::assertTrue($equality->equals(5, 5));
        self::assertFalse($equality->equals(new Dog('x'), new Dog('x')));
        self::assertTrue($equality->equals($dog, $dog));
        self::assertSame('i:5', $equality->hashKey(5));
        self::assertNotNull($equality->hashKey($dog));
    }

    public function testWithLeavesTheOriginalUntouched(): void
    {
        $cats = ComparatorEquality::of(Cat::class, static fn (Cat $left, Cat $right): bool => true);
        $cats->with(Dog::class, static fn (Dog $left, Dog $right): bool => true);

        self::assertFalse($cats->equals(new Dog('a'), new Dog('a')));
    }

    public function testFirstRegisteredClassDecides(): void
    {
        $equality = ComparatorEquality::of(Countable::class, static fn (Countable $left, Countable $right): bool => true)
            ->with(ArrayObject::class, static fn (ArrayObject $left, ArrayObject $right): bool => false)
        ;

        self::assertTrue($equality->equals(new ArrayObject(), new ArrayObject()));
    }

    public function testFirstMatchingClassDecides(): void
    {
        $equality = ComparatorEquality::of(Cat::class, static fn (Cat $left, Cat $right): bool => true)
            ->with(Dog::class, static fn (Dog $left, Dog $right): bool => false)
        ;

        self::assertTrue($equality->equals(new Cat('a'), new Cat('b')));
        self::assertFalse($equality->equals(new Dog('a'), new Dog('a')));
    }
}
