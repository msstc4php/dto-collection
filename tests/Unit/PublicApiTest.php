<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit;

use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\MutableListOf;
use MSSTC4PHP\DtoCollection\Tests\Fixture\IntegerList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\ItemList;
use MSSTC4PHP\DtoCollection\Tests\Fixture\MutableItemList;
use NoDiscard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

final class PublicApiTest extends TestCase
{
    /** A result that may be ignored: each() returns the list itself, the removals report what they removed. */
    private const MAY_DISCARD = ['each', 'remove', 'removeElement', 'removeIf', 'pop', 'shift'];

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function classes(): iterable
    {
        foreach ([ListOf::class, MutableListOf::class, ItemList::class, IntegerList::class, MutableItemList::class] as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('classes')]
    public function testEveryValueReturningPublicMethodIsNoDiscard(string $class): void
    {
        foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $type = $method->getReturnType();
            if ($method->isConstructor() || in_array($method->getName(), self::MAY_DISCARD, true) || ($type instanceof ReflectionNamedType && $type->getName() === 'void')) {
                self::assertEmpty($method->getAttributes(NoDiscard::class), $class . '::' . $method->getName() . '() must not be #[NoDiscard]');

                continue;
            }
            self::assertNotEmpty($method->getAttributes(NoDiscard::class), $class . '::' . $method->getName() . '() lacks #[NoDiscard]');
        }
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('classes')]
    public function testNoInstanceMethodDeclaresStaticReturn(string $class): void
    {
        foreach ((new ReflectionClass($class))->getMethods() as $method) {
            $type = $method->getReturnType();
            if (!$method->isStatic()) {
                self::assertFalse($type instanceof ReflectionNamedType && $type->getName() === 'static', $class . '::' . $method->getName() . '() returns static');
            }
        }
    }
}
