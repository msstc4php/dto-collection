<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Contract\ImmutableContract;

final class ListOfImmutableTest extends ImmutableContract
{
    use ListOfLists;

    public function testWithCoercesOnGeneric(): void
    {
        self::assertSame([1.0, 2.0], ListOf::floats([1.0])->with(2)->toArray());
        self::assertSame([2.0, 1.0], ListOf::floats([1.0])->withPrepended(2)->toArray());
        self::assertSame([2.0], ListOf::floats([1.0])->withSet(0, 2)->toArray());
        self::assertSame([2.0, 1.0], ListOf::floats([1.0])->withInsertedAt(0, 2)->toArray());
        self::assertSame([], ListOf::floats([1.0])->withoutElement(1)->toArray());
        self::assertSame([3.0], ListOf::floats([1.0])->withReplaced(1, 3)->toArray());
    }

    public function testWithRejectsWrongTypeOnGeneric(): void
    {
        $this->expectException(ElementTypeMismatch::class);
        $this->expectExceptionMessage('Element #1 must be int, string given');

        $list = ListOf::integers([1])->with(2, 'x');
    }
}
