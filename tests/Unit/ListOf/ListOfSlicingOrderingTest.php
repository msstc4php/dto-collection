<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Contract\SlicingOrderingContract;

final class ListOfSlicingOrderingTest extends SlicingOrderingContract
{
    use ListOfLists;

    public function testFilterWithInternalFunction(): void
    {
        self::assertSame(['a'], ListOf::ofType('int|string', [1, 'a'])->filter(is_string(...))->toArray());
    }

    public function testTransformWithInternalFunction(): void
    {
        self::assertSame(['A', 'B'], ListOf::strings(['a', 'b'])->transform(strtoupper(...))->toArray());
    }

    public function testTransformChecksType(): void
    {
        $this->expectExceptionMessage('Element #0 must be int, string given');

        // Reason: a mapper returning another type is the input under test.
        $list = ListOf::integers([1])->transform(static fn (int $element): string => 'x'); // @phpstan-ignore argument.type
    }
}
