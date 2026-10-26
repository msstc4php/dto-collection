<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\Exception\UnsupportedOperation;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use PHPUnit\Framework\TestCase;

final class ListOfNumericTest extends TestCase
{
    public function testSumAverageMinMax(): void
    {
        $list = ListOf::integers([3, 1, 2]);

        self::assertSame(6, $list->sum());
        self::assertSame(2.0, $list->average());
        self::assertSame(1, $list->min());
        self::assertSame(3, $list->max());
    }

    public function testEmpty(): void
    {
        $list = ListOf::integers([]);

        self::assertSame(0, $list->sum());
        self::assertNull($list->average());
        self::assertNull($list->min());
        self::assertNull($list->max());
    }

    public function testFloatsAndMixedNumbers(): void
    {
        self::assertSame(3.5, ListOf::floats([1, 2.5])->sum());
        self::assertSame(1.75, ListOf::floats([1, 2.5])->average());
        self::assertSame(2.5, ListOf::ofType('int|float', [1, 2.5])->max());
        self::assertSame(1, ListOf::ofType('int|float', [1, 2.5])->min());
    }

    public function testNumericOnNonNumericThrows(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('sum() needs a numeric element type; the list holds string');

        $sum = ListOf::strings(['a'])->sum();
    }

    public function testEveryNumericMethodChecksTheType(): void
    {
        $list = ListOf::of(Item::class, [new Item('a', 1)]);
        $operations = [
            'sum' => $list->sum(...),
            'average' => $list->average(...),
            'min' => $list->min(...),
            'max' => $list->max(...),
        ];
        foreach ($operations as $method => $operation) {
            try {
                $result = $operation();
                self::fail($method . '() accepted a non-numeric list');
            } catch (UnsupportedOperation $exception) {
                self::assertStringStartsWith($method . '()', $exception->getMessage());
            }
        }
    }

    public function testJoin(): void
    {
        self::assertSame('a,b', ListOf::strings(['a', 'b'])->join(','));
        self::assertSame('ab', ListOf::strings(['a', 'b'])->join());
        self::assertSame('', ListOf::strings([])->join(','));
    }

    public function testJoinNeedsStrings(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('join() needs the element type string; the list holds int');

        $joined = ListOf::integers([1])->join();
    }

    public function testReduceWithInternalTwoParameterFunction(): void
    {
        self::assertSame(2.0, ListOf::floats([2.0, 4.0])->reduce(fdiv(...), 16.0));
    }
}
