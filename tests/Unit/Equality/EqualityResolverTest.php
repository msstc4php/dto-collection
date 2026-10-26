<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Equality;

use Countable;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\EqualityResolver;
use MSSTC4PHP\DtoCollection\Equality\StrictEquality;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Cat;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Dog;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;
use PHPUnit\Framework\TestCase;
use Traversable;

final class EqualityResolverTest extends TestCase
{
    public function testResolverPicksEquatable(): void
    {
        $equality = EqualityResolver::forType(ElementType::ofClass(Item::class));

        self::assertTrue($equality->equals(new Item('a', 1), new Item('a', 2)));
        self::assertFalse($equality->equals(new Item('a', 1), new Item('b', 1)));
    }

    public function testResolverStrictWithoutEquatable(): void
    {
        self::assertInstanceOf(StrictEquality::class, EqualityResolver::forType(ElementType::ofClass(Tag::class)));
        self::assertInstanceOf(StrictEquality::class, EqualityResolver::forType(ElementType::integer()));
    }

    public function testResolverUnion(): void
    {
        $equality = EqualityResolver::forType(ElementType::parse(Cat::class . '|' . Dog::class . '|null'));

        self::assertTrue($equality->equals(new Cat('x'), new Cat('x')));
        self::assertFalse($equality->equals(new Dog('x'), new Dog('x')));
        self::assertFalse($equality->equals(new Cat('x'), new Dog('x')));
        self::assertTrue($equality->equals(null, null));
    }

    public function testIntersectionBranchesUseIdentity(): void
    {
        self::assertInstanceOf(StrictEquality::class, EqualityResolver::forType(ElementType::intersection(Countable::class, Traversable::class)));
    }

    public function testResolverUnionOfTwoEquatables(): void
    {
        $equality = EqualityResolver::forType(ElementType::parse(Item::class . '|' . Cat::class));

        self::assertTrue($equality->equals(new Item('a', 1), new Item('a', 2)));
        self::assertTrue($equality->equals(new Cat('x'), new Cat('x')));
    }
}
