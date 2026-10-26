<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Mutable;

use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\MutableListOf;
use PHPUnit\Framework\TestCase;

final class MutableListOfTest extends TestCase
{
    public function testPushCoercesOnGeneric(): void
    {
        $list = MutableListOf::floats();
        $list->push(3);
        $list->unshift(1);
        $list->set(2, 4);
        $list->insertAt(1, 2);

        self::assertSame([1.0, 2.0, 3.0, 4.0], $list->toArray());
        self::assertTrue($list->removeElement(2));
        self::assertSame([1.0, 3.0, 4.0], $list->toArray());
    }

    public function testFactories(): void
    {
        self::assertSame([1], MutableListOf::integers([1])->toArray());
        self::assertSame(['a'], MutableListOf::strings(['a'])->toArray());
        self::assertSame([true], MutableListOf::booleans([true])->toArray());
        self::assertSame([null], MutableListOf::ofType('?int', [null])->toArray());
        self::assertSame(3, MutableListOf::integers([1, 2])->sum());
        self::assertSame('a-b', MutableListOf::strings(['a', 'b'])->join('-'));
    }

    public function testPushRejectsWrongType(): void
    {
        $list = MutableListOf::integers([1]);

        $this->expectException(ElementTypeMismatch::class);
        $this->expectExceptionMessage('Element #1 must be int, string given');
        $list->push(2, 'x');
    }

    public function testRejectedPushLeavesTheListUnchanged(): void
    {
        $list = MutableListOf::integers([1]);
        try {
            $list->push(2, 'x');
        } catch (ElementTypeMismatch) {
        }

        self::assertSame([1], $list->toArray());
    }

    public function testRemoveElementRejectsWrongType(): void
    {
        $this->expectException(ElementTypeMismatch::class);

        MutableListOf::integers([1])->removeElement('1');
    }

    public function testMixedRemovals(): void
    {
        $list = MutableListOf::integers([1, 2, 3]);

        self::assertSame(2, $list->remove(1));
        self::assertSame(3, $list->pop());
        self::assertSame(1, $list->shift());
        self::assertNull($list->shift());
    }
}
