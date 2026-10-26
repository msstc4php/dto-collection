<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit;

use ArrayObject;
use Countable;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Exception\CollectionException;
use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\Exception\InvalidElementType;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Cat;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Dog;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use PHPUnit\Framework\TestCase;
use Traversable;

final class ElementTypeTest extends TestCase
{
    public function testScalarsAcceptOnlyTheirType(): void
    {
        $integer = ElementType::integer();

        self::assertTrue($integer->accepts(1));
        self::assertFalse($integer->accepts(1.0));
        self::assertFalse($integer->accepts('1'));
        self::assertFalse($integer->accepts(true));
        self::assertFalse($integer->accepts(null));
        self::assertTrue(ElementType::string()->accepts(''));
        self::assertFalse(ElementType::string()->accepts(1));
        self::assertTrue(ElementType::boolean()->accepts(false));
        self::assertFalse(ElementType::float()->accepts('1.5'));
    }

    public function testFloatCoercesIntLikeStrictTypes(): void
    {
        self::assertSame(1.0, ElementType::float()->coerce(1, 0));
        self::assertSame(1.5, ElementType::float()->coerce(1.5, 0));
        self::assertTrue(ElementType::float()->accepts(1));
        self::assertSame(1, ElementType::union(ElementType::integer(), ElementType::float())->coerce(1, 0));
        self::assertSame(1, ElementType::union(ElementType::float(), ElementType::integer())->coerce(1, 0));
        self::assertSame(1.0, ElementType::float()->nullable()->coerce(1, 0));
    }

    public function testBooleanRejectsInts(): void
    {
        self::assertFalse(ElementType::boolean()->accepts(1));
        self::assertFalse(ElementType::float()->accepts(true));
    }

    public function testClassAcceptsSubtypes(): void
    {
        self::assertTrue(ElementType::ofClass(Countable::class)->accepts(new ArrayObject()));
        self::assertFalse(ElementType::ofClass(Countable::class)->accepts(new Dog('x')));
        self::assertFalse(ElementType::ofClass(Countable::class)->accepts('Countable'));
    }

    public function testIntersectionNeedsAll(): void
    {
        $type = ElementType::intersection(Countable::class, Traversable::class);
        $countableOnly = new class implements Countable {
            public function count(): int
            {
                return 0;
            }
        };

        self::assertTrue($type->accepts(new ArrayObject()));
        self::assertFalse($type->accepts($countableOnly));
        self::assertSame('Countable&Traversable', $type->describe());
        self::assertSame([[Countable::class, Traversable::class]], $type->objectBranches());
    }

    public function testIntersectionNeedsTwoDistinctClasses(): void
    {
        $this->expectException(InvalidElementType::class);

        ElementType::intersection(Countable::class, '\Countable');
    }

    public function testNullable(): void
    {
        $type = ElementType::ofClass(Item::class)->nullable();

        self::assertTrue($type->accepts(null));
        self::assertTrue($type->isNullable());
        self::assertFalse(ElementType::ofClass(Item::class)->isNullable());
        self::assertSame(Item::class . '|null', $type->describe());
        self::assertSame(Item::class . '|null', $type->nullable()->describe());
        self::assertNull($type->coerce(null, 0));
    }

    public function testUnionDescribe(): void
    {
        $type = ElementType::union(ElementType::ofClass(Cat::class), ElementType::ofClass(Dog::class))->nullable();

        self::assertSame(Cat::class . '|' . Dog::class . '|null', $type->describe());
        self::assertSame([[Cat::class], [Dog::class]], $type->objectBranches());
        self::assertSame('int|string|null', ElementType::union(ElementType::integer()->nullable(), ElementType::string())->describe());
    }

    public function testUnionRejectsDuplicatesAndEmpty(): void
    {
        try {
            ElementType::union(ElementType::integer(), ElementType::integer());
            self::fail('duplicate branch accepted');
        } catch (InvalidElementType) {
        }

        $this->expectException(InvalidElementType::class);
        // Reason: an empty union has no U to infer, which is the very case under test.
        ElementType::union(); // @phpstan-ignore argument.templateType
    }

    public function testCoerceMismatchMessage(): void
    {
        $this->expectException(ElementTypeMismatch::class);
        $this->expectExceptionMessage('Element #3 must be int, string given');

        ElementType::integer()->coerce('x', 3);
    }

    public function testCoerceMismatchNamesTheClass(): void
    {
        $this->expectExceptionMessage('Element #0 must be ' . Item::class . '|null, ' . Dog::class . ' given');

        ElementType::ofClass(Item::class)->nullable()->coerce(new Dog('x'), 0);
    }

    public function testExceptionsAreCollectionExceptions(): void
    {
        $this->expectException(CollectionException::class);

        ElementType::integer()->coerce('x', 0);
    }

    public function testOfClassRejectsUnknownClass(): void
    {
        $this->expectException(InvalidElementType::class);
        $this->expectExceptionMessage('No\Such');

        // Reason: the test feeds a name that is deliberately not a class-string.
        ElementType::ofClass('No\Such'); // @phpstan-ignore argument.type
    }

    public function testOfClassNormalisesTheName(): void
    {
        self::assertSame(Item::class, ElementType::ofClass('\\' . strtolower(Item::class))->describe());
    }

    public function testIsNumericAndIsString(): void
    {
        self::assertTrue(ElementType::union(ElementType::integer(), ElementType::float())->isNumeric());
        self::assertTrue(ElementType::integer()->isNumeric());
        self::assertTrue(ElementType::float()->isNumeric());
        self::assertFalse(ElementType::union(ElementType::integer(), ElementType::string())->isNumeric());
        self::assertFalse(ElementType::integer()->nullable()->isNumeric());
        self::assertTrue(ElementType::string()->isString());
        self::assertFalse(ElementType::string()->nullable()->isString());
        self::assertFalse(ElementType::ofClass(Item::class)->isNumeric());
        self::assertFalse(ElementType::ofClass(Item::class)->isString());
    }

    public function testEquals(): void
    {
        self::assertTrue(ElementType::parse('int|null')->equals(ElementType::integer()->nullable()));
        self::assertTrue(ElementType::parse(Dog::class . '|' . Cat::class)->equals(ElementType::union(ElementType::ofClass(Cat::class), ElementType::ofClass(Dog::class))));
        self::assertFalse(ElementType::integer()->equals(ElementType::integer()->nullable()));
        self::assertFalse(ElementType::integer()->equals(ElementType::float()));
    }

    public function testScalarDescriptions(): void
    {
        self::assertSame('int', ElementType::integer()->describe());
        self::assertSame('string', ElementType::string()->describe());
        self::assertSame('float', ElementType::float()->describe());
        self::assertSame('bool', ElementType::boolean()->describe());
        self::assertSame([], ElementType::integer()->objectBranches());
    }
}
