<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Equality;

use MSSTC4PHP\DtoCollection\Equality\StrictEquality;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;
use PHPUnit\Framework\TestCase;

final class StrictEqualityTest extends TestCase
{
    public function testStrictScalars(): void
    {
        $equality = new StrictEquality();

        self::assertTrue($equality->equals(1, 1));
        self::assertFalse($equality->equals(1, 1.0));
        self::assertFalse($equality->equals('1', 1));
        self::assertTrue($equality->equals(null, null));
        self::assertFalse($equality->equals(null, false));
        self::assertTrue($equality->equals(-0.0, 0.0));
        self::assertFalse($equality->equals(NAN, NAN));
        self::assertTrue($equality->equals(INF, INF));
    }

    public function testStrictObjectsByIdentity(): void
    {
        $equality = new StrictEquality();
        $tag = new Tag('a');

        self::assertFalse($equality->equals(new Tag('a'), new Tag('a')));
        self::assertTrue($equality->equals($tag, $tag));
        self::assertSame($equality->hashKey($tag), $equality->hashKey($tag));
        self::assertNotSame($equality->hashKey($tag), $equality->hashKey(new Tag('a')));
    }

    public function testHashKeys(): void
    {
        $equality = new StrictEquality();

        self::assertSame($equality->hashKey(-0.0), $equality->hashKey(0.0));
        self::assertNull($equality->hashKey(NAN));
        self::assertSame('n', $equality->hashKey(null));
        self::assertSame('b:0', $equality->hashKey(false));
        self::assertSame('b:1', $equality->hashKey(true));
        self::assertSame('i:5', $equality->hashKey(5));
        self::assertSame('s:5', $equality->hashKey('5'));
        self::assertSame('f:5.0', $equality->hashKey(5.0));
        self::assertSame('f:0', $equality->hashKey(-0.0));
        self::assertSame('f:INF', $equality->hashKey(INF));
        self::assertNotSame($equality->hashKey(0.1), $equality->hashKey(0.1 + 1e-17 * 3));
    }
}
