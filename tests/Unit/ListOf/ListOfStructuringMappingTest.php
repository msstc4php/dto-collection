<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\ListOf;

use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\Tests\Contract\StructuringMappingContract;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Parity;

final class ListOfStructuringMappingTest extends StructuringMappingContract
{
    use ListOfLists;

    public function testSelectorsMustReturnIntOrString(): void
    {
        $list = ListOf::floats([1.5, 1.7]);
        // Reason: selectors returning floats or null are the input under test.
        $calls = [
            'groupBy' => static fn (): array => $list->groupBy(static fn (float $value): float => $value), // @phpstan-ignore argument.type
            'keyBy' => static fn (): array => $list->keyBy(static fn (float $value): float => $value), // @phpstan-ignore argument.type
            'countBy' => static fn (): array => $list->countBy(static fn (float $value): float => $value), // @phpstan-ignore argument.type
            'uniqueBy' => static fn (): object => $list->uniqueBy(static fn (float $value): float => $value), // @phpstan-ignore argument.type
            'diffBy' => static fn (): object => $list->diffBy(static fn (float $value): float => $value, [1.5]), // @phpstan-ignore argument.type
            'intersectBy' => static fn (): object => $list->intersectBy(static fn (float $value): ?float => null, [1.5]), // @phpstan-ignore argument.type
        ];
        foreach ($calls as $method => $call) {
            try {
                $result = $call();
                self::fail($method . '() accepted a non-key');
            } catch (InvalidArgument $exception) {
                self::assertStringStartsWith($method . '() needs a selector that returns int or string', $exception->getMessage());
            }
        }
    }

    public function testBackedEnumKeysUseTheirValue(): void
    {
        $groups = ListOf::integers([1, 2, 3])->groupBy(static fn (int $value): Parity => $value % 2 === 0 ? Parity::Even : Parity::Odd);

        self::assertSame(['odd', 'even'], array_keys($groups));
    }
}
