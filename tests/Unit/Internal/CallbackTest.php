<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Internal;

use MSSTC4PHP\DtoCollection\Internal\Callback;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Text;
use PHPUnit\Framework\TestCase;

final class CallbackTest extends TestCase
{
    public function testPassesIndexToTwoParameterClosure(): void
    {
        self::assertSame(7, Callback::invoke(static fn (string $element, int $index): int => $index, 'a', 7));
    }

    public function testPassesIndexToOptionalSecondParameterOfAClosure(): void
    {
        self::assertSame(7, Callback::invoke(static fn (string $element, int $index = 0): int => $index, 'a', 7));
    }

    public function testOmitsIndexForSingleParameterClosure(): void
    {
        self::assertSame('a', Callback::invoke(static fn (string $element): string => $element, 'a', 7));
    }

    public function testOmitsIndexForInternalSingleArgFunction(): void
    {
        self::assertSame('A', Callback::invoke(strtoupper(...), 'a', 7));
        self::assertTrue(Callback::invoke(is_string(...), 'a', 7));
    }

    public function testOmitsIndexForInternalFunctionWithOptionalSecondParameter(): void
    {
        // trim('a ', 7) would be a TypeError under strict_types.
        // Reason: PHPStan rightly sees trim()'s optional string $characters; the runtime is what is under test.
        self::assertSame('a', Callback::invoke(trim(...), ' a ', 7)); // @phpstan-ignore argument.type
    }

    public function testPassesIndexToVariadic(): void
    {
        self::assertSame(2, Callback::invoke(static fn (mixed ...$arguments): int => count($arguments), 'a', 1));
    }

    public function testTakesCountsArguments(): void
    {
        self::assertTrue(Callback::takes(static fn ($carry, $element, $index): int => 0, 3));
        self::assertFalse(Callback::takes(static fn ($carry, $element): int => 0, 3));
        self::assertTrue(Callback::takes(str_replace(...), 3));
        self::assertFalse(Callback::takes(str_pad(...), 3));
    }

    public function testRepeatedCallsUseTheSameAnswer(): void
    {
        $closure = static fn (string $element, int $index): string => $element . $index;

        self::assertSame('a0', Callback::invoke($closure, 'a', 0));
        self::assertSame('b1', Callback::invoke($closure, 'b', 1));
    }

    public function testOmitsIndexForUserCallableWithOptionalNonIntSecondParameter(): void
    {
        // Reason: PHPStan rightly sees the optional bool; the runtime is what is under test.
        self::assertSame('a', Callback::invoke(Text::normalised(...), ' a ', 7)); // @phpstan-ignore argument.type
    }

    public function testOmitsIndexForTypedVariadicThatCannotTakeAnInt(): void
    {
        // Reason: PHPStan rightly sees the string variadic; the runtime is what is under test.
        self::assertSame('a', Callback::invoke(Text::joined(...), 'a', 7)); // @phpstan-ignore argument.type
    }

    public function testPassesIndexToOptionalUntypedOrNumericParameters(): void
    {
        self::assertSame(7, Callback::invoke(static fn (string $element, $index = null): mixed => $index, 'a', 7));
        self::assertSame(7.0, Callback::invoke(static fn (string $element, float $index = 0.0): float => $index, 'a', 7));
        self::assertSame('7', Callback::invoke(static fn (string $element, int|string $index = 'none'): string => (string) $index, 'a', 7));
    }

    public function testPassesIndexToRequiredParameterWhateverItsType(): void
    {
        self::assertSame('aa', Callback::invoke(str_repeat(...), 'a', 2));
    }

    public function testARequiredParameterBeforeAVariadicGetsTheIndex(): void
    {
        self::assertSame(7, Callback::invoke(static fn (string $element, int $index, string ...$rest): int => $index, 'a', 7));
    }

    public function testOmitsIndexForAUnionThatCannotTakeAnInt(): void
    {
        // Reason: PHPStan rightly sees the optional string|bool; the runtime is what is under test.
        self::assertSame('a', Callback::invoke(static fn (string $element, string|bool $flag = false): string => $element, 'a', 7)); // @phpstan-ignore argument.type
    }
}
