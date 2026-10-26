<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Contract\SearchingContract;

final class ListOfSearchingTest extends SearchingContract
{
    use ListOfLists;

    public function testFirstDoesNotTreatFalsyAsMissing(): void
    {
        self::assertSame(0, ListOf::integers([0])->first());
        self::assertSame('', ListOf::strings([''])->last());
        self::assertFalse(ListOf::booleans([false])->firstOrFail());
    }

    public function testContainsRejectsWrongTypeOnGeneric(): void
    {
        $this->expectException(ElementTypeMismatch::class);

        $contains = ListOf::integers([1])->contains('1');
    }

    public function testContainsAllRejectsWrongTypeWithItsPosition(): void
    {
        $this->expectExceptionMessage('Element #1 must be int, string given');

        $contains = ListOf::integers([1])->containsAll(1, 'x');
    }

    public function testContainsAnyAndPositionOfCoerce(): void
    {
        self::assertTrue(ListOf::floats([1.0])->containsAny(1));
        self::assertSame(0, ListOf::floats([1.0])->positionOf(1));
        self::assertTrue(ListOf::floats([1.0])->contains(1));
    }
}
