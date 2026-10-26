<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Pipe;

use MSSTC4PHP\DtoCollection\ImmutableList;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\ReadableList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use MSSTC4PHP\DtoCollection\Tests\Fixture\ItemList;
use NoDiscard;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;

use function MSSTC4PHP\DtoCollection\Pipe\averageBy;
use function MSSTC4PHP\DtoCollection\Pipe\count;
use function MSSTC4PHP\DtoCollection\Pipe\filter;
use function MSSTC4PHP\DtoCollection\Pipe\first;
use function MSSTC4PHP\DtoCollection\Pipe\mapTo;
use function MSSTC4PHP\DtoCollection\Pipe\reduce;
use function MSSTC4PHP\DtoCollection\Pipe\sliced;
use function MSSTC4PHP\DtoCollection\Pipe\sortedBy;
use function MSSTC4PHP\DtoCollection\Pipe\take;
use function MSSTC4PHP\DtoCollection\Pipe\windowed;
use function MSSTC4PHP\DtoCollection\Pipe\without;

final class FunctionsTest extends TestCase
{
    private const NAMESPACE = 'MSSTC4PHP\DtoCollection\Pipe\\';

    public function testPipeComposition(): void
    {
        $result = ListOf::integers([5, 1, 4])->pipe(filter(static fn (int $element): bool => $element > 1), sortedBy(static fn (int $element): int => $element), take(1));

        self::assertInstanceOf(ListOf::class, $result);
        self::assertSame([4], $result->toArray());
    }

    public function testFunctionsKeepTheListClassAndPassArguments(): void
    {
        $a = new Item('a', 1);
        $b = new Item('b', 3);
        $items = ItemList::of($a, $b);

        self::assertSame([$b, $a], sortedBy(static fn (Item $item): int => $item->score, descending: true)($items)->toArray());
        self::assertInstanceOf(ItemList::class, take(1)($items));
        self::assertSame([$b], sliced(-1)($items)->toArray());
        self::assertSame([$a], sliced(0, 1)($items)->toArray());
        self::assertSame([$b], without(0)($items)->toArray());
        self::assertSame($a, first()($items));
        self::assertSame(2, count()($items));
        self::assertSame(2.0, averageBy(static fn (Item $item): int => $item->score)($items));
        self::assertSame(4, reduce(static fn (int $carry, Item $item): int => $carry + $item->score, 0)($items));
        self::assertSame([1, 3], mapTo('int', static fn (Item $item): int => $item->score)($items)->toArray());
        self::assertCount(1, windowed(2, partial: false)($items));
    }

    public function testEveryInterfaceMethodHasAFunction(): void
    {
        $methods = ['count'];
        foreach ([ReadableList::class, ImmutableList::class] as $interface) {
            foreach ((new ReflectionClass($interface))->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() === $interface && $method->getName() !== 'pipe') {
                    $methods[] = $method->getName();
                }
            }
        }

        foreach ($methods as $method) {
            self::assertTrue(function_exists(self::NAMESPACE . $method), 'Missing ' . self::NAMESPACE . $method . '()');
        }
        self::assertCount(66, $methods);
    }

    public function testFunctionsMirrorTheMethodParameters(): void
    {
        foreach ([ReadableList::class, ImmutableList::class] as $interface) {
            foreach ((new ReflectionClass($interface))->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() !== $interface || $method->getName() === 'pipe') {
                    continue;
                }
                $function = new ReflectionFunction(self::NAMESPACE . $method->getName());
                self::assertSame(
                    array_map(static fn (ReflectionParameter $parameter): string => $parameter->getName(), $method->getParameters()),
                    array_map(static fn (ReflectionParameter $parameter): string => $parameter->getName(), $function->getParameters()),
                    $method->getName(),
                );
            }
        }
    }

    public function testFunctionsAreNoDiscard(): void
    {
        foreach (get_defined_functions()['user'] as $function) {
            if (str_starts_with($function, strtolower(self::NAMESPACE))) {
                self::assertNotEmpty((new ReflectionFunction($function))->getAttributes(NoDiscard::class), $function);
            }
        }
    }
}
