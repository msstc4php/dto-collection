<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Attribute;

use MSSTC4PHP\DtoCollection\Attribute\TypedCollection;
use MSSTC4PHP\DtoCollection\Attribute\Variant;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\Exception\InvalidElementType;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;

#[TypedCollection('App\Collection\Things', Variant::Immutable)]
#[TypedCollection('App\Document\ThingCollection', Variant::Mutable, nullable: true, elementType: '\Countable')]
final class TypedCollectionTest extends TestCase
{
    public function testRepeatableOnClass(): void
    {
        $attributes = array_map(
            static fn (ReflectionAttribute $attribute): TypedCollection => $attribute->newInstance(),
            (new ReflectionClass(self::class))->getAttributes(TypedCollection::class),
        );

        self::assertCount(2, $attributes);
        self::assertSame('App\Collection\Things', $attributes[0]->class);
        self::assertSame(Variant::Immutable, $attributes[0]->variant);
        self::assertFalse($attributes[0]->nullable);
        self::assertNull($attributes[0]->elementType);
        self::assertSame(Variant::Mutable, $attributes[1]->variant);
        self::assertTrue($attributes[1]->nullable);
        self::assertSame('\Countable', $attributes[1]->elementType);
    }

    public function testVariantValues(): void
    {
        self::assertSame(['immutable', 'mutable', 'odm'], array_map(static fn (Variant $variant): string => $variant->value, Variant::cases()));
    }

    public function testRejectsInvalidElementType(): void
    {
        $this->expectException(InvalidElementType::class);

        new TypedCollection('X', Variant::Mutable, elementType: 'mixed');
    }

    public function testRejectsNullableOdmCollections(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Doctrine ODM does not store null elements');

        new TypedCollection('App\Document\Things', Variant::Odm, nullable: true);
    }

    public function testRejectsNullableOdmElementType(): void
    {
        $this->expectException(InvalidArgument::class);

        new TypedCollection('App\Document\Things', Variant::Odm, elementType: '?\Countable');
    }

    public function testAcceptsOdmCollections(): void
    {
        self::assertSame(Variant::Odm, (new TypedCollection('App\Document\Things', Variant::Odm, elementType: '\Countable'))->variant);
    }

    public function testRejectsEmptyClass(): void
    {
        $this->expectException(InvalidArgument::class);

        new TypedCollection('  ', Variant::Mutable);
    }

    public function testAcceptsOdmCollectionsWithoutElementType(): void
    {
        self::assertNull((new TypedCollection('App\\Document\\Things', Variant::Odm))->elementType);
    }
}
