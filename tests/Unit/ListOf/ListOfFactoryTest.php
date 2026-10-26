<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use ArrayIterator;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\ComparatorEquality;
use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;
use PHPUnit\Framework\TestCase;

final class ListOfFactoryTest extends TestCase
{
    public function testFactoriesValidate(): void
    {
        $this->expectException(ElementTypeMismatch::class);
        $this->expectExceptionMessage('Element #1 must be int, string given');

        // Reason: the wrong element type is the input under test.
        $list = ListOf::integers([1, 'x']); // @phpstan-ignore argument.type
    }

    public function testOfValidatesTheClass(): void
    {
        $this->expectExceptionMessage('Element #0 must be ' . Item::class . ', ' . Tag::class . ' given');

        $list = ListOf::of(Item::class, [new Tag('x')]);
    }

    public function testKeysDiscarded(): void
    {
        self::assertSame([1, 2], ListOf::integers([5 => 1, 'a' => 2])->toArray());
    }

    public function testAcceptsAnyIterable(): void
    {
        self::assertSame(['a', 'b'], ListOf::strings(new ArrayIterator(['x' => 'a', 'y' => 'b']))->toArray());
        self::assertSame([true, false], ListOf::booleans((static function () {
            yield true;
            yield false;
        })())->toArray());
    }

    public function testFloatsCoerceInts(): void
    {
        self::assertSame([1.0, 2.0, 2.5], ListOf::floats([1, 2, 2.5])->toArray());
    }

    public function testEmptyFactories(): void
    {
        self::assertSame([], ListOf::integers()->toArray());
        self::assertSame([], ListOf::strings()->toArray());
        self::assertSame([], ListOf::floats()->toArray());
        self::assertSame([], ListOf::booleans()->toArray());
        self::assertSame([], ListOf::of(Item::class)->toArray());
        self::assertSame([], ListOf::ofType('int')->toArray());
    }

    public function testOfTypeString(): void
    {
        self::assertSame([null, 1], ListOf::ofType('?int', [null, 1])->toArray());
    }

    public function testOfTypeObject(): void
    {
        self::assertSame(['a'], ListOf::ofType(ElementType::string(), ['a'])->toArray());
    }

    public function testDerivedEqualityFollowsEquatable(): void
    {
        self::assertTrue(ListOf::ofType(Item::class, [new Item('a', 1)])->equals(ListOf::ofType(Item::class, [new Item('a', 2)])));
    }

    public function testExplicitEqualityWins(): void
    {
        $byName = ComparatorEquality::of(Tag::class, static fn (Tag $left, Tag $right): bool => $left->name === $right->name);

        self::assertTrue(ListOf::of(Tag::class, [new Tag('t')], $byName)->equals(ListOf::of(Tag::class, [new Tag('t')])));
        self::assertTrue(ListOf::ofType(Tag::class, [new Tag('t')], $byName)->equals(ListOf::ofType(Tag::class, [new Tag('t')])));
        self::assertFalse(ListOf::of(Tag::class, [new Tag('t')])->equals(ListOf::of(Tag::class, [new Tag('t')], $byName)));
    }

    public function testGetDelegatesToTheElement(): void
    {
        $list = ListOf::integers([10, 20]);

        self::assertSame(20, $list->get(1));
        self::assertNull($list->get(2));
        self::assertNull($list->get(-1));
    }
}
