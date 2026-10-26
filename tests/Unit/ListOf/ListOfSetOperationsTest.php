<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use ArrayIterator;
use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Contract\SetOperationsContract;

final class ListOfSetOperationsTest extends SetOperationsContract
{
    use ListOfLists;

    public function testUniqueFloats(): void
    {
        $unique = ListOf::floats([0.0, -0.0, NAN, NAN, 1, 1.0])->unique()->toArray();

        self::assertCount(4, $unique);
        self::assertSame(0.0, $unique[0]);
        self::assertNan($unique[1]);
        self::assertNan($unique[2]);
        self::assertSame(1.0, $unique[3]);
    }

    public function testIntegerSets(): void
    {
        self::assertSame([1, 1], ListOf::integers([1, 1, 2])->diff([2])->toArray());
        self::assertSame([3], ListOf::integers([1, 2, 3])->intersect([2, 3], [3])->toArray());
        self::assertSame([1, 2, 3], ListOf::integers([1, 2])->union([2, 3])->toArray());
        self::assertSame([1, 2, 2], ListOf::integers([1, 2])->merge([2])->toArray());
        self::assertSame([1], ListOf::integers([1, 2])->diff(ListOf::integers([2]))->toArray());
        self::assertSame([1], ListOf::integers([1, 2])->diff(new ArrayIterator(['k' => 2]))->toArray());
        self::assertSame(['1'], ListOf::strings(['1', '2'])->diff(['2'])->toArray());
    }

    public function testDiffCoercesOthers(): void
    {
        $this->expectException(ElementTypeMismatch::class);

        // Reason: the wrong element type is the input under test.
        $list = ListOf::integers([1])->diff(['1']); // @phpstan-ignore argument.type
    }

    public function testMergeCoercesOthers(): void
    {
        self::assertSame([1.0, 2.0], ListOf::floats([1.0])->merge([2])->toArray());

        $this->expectException(ElementTypeMismatch::class);
        // Reason: the wrong element type is the input under test.
        $list = ListOf::integers([1])->merge([1.5]); // @phpstan-ignore argument.type
    }
}
