<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Internal;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use WeakMap;

/**
 * Calls user callbacks with the element index only when they ask for it.
 *
 * @internal
 */
final class Callback
{
    /** @var WeakMap<Closure, array{2: bool, 3: bool}>|null */
    private static ?WeakMap $signatures = null;

    /**
     * @template E
     * @template R
     *
     * @param Closure(E, int): R $callback
     * @param E $element
     *
     * @return R
     */
    public static function invoke(Closure $callback, mixed $element, int $index): mixed
    {
        $arguments = self::takes($callback, 2) ? [$element, $index] : [$element];

        // Reason: dropping the index for callbacks that do not declare it is this method's purpose; the closure type
        // states the full (element, index) signature that callers may rely on.
        return $callback(...$arguments); // @phpstan-ignore arguments.count
    }

    /**
     * Whether $callback takes an argument at position $arguments without a TypeError.
     *
     * A required parameter always gets it. An optional one gets it only when its type accepts an int: passing an index
     * into trim()'s optional string $characters, or into an optional bool flag, would fail under strict_types.
     *
     * @param Closure(never, never, never): mixed $callback
     * @param 2|3 $arguments
     */
    public static function takes(Closure $callback, int $arguments): bool
    {
        self::$signatures ??= new WeakMap();
        $takes = self::$signatures[$callback] ??= self::signature($callback);

        return $takes[$arguments];
    }

    /**
     * @return array{2: bool, 3: bool} whether the callback takes 2 and 3 arguments
     */
    private static function signature(Closure $callback): array
    {
        $parameters = (new ReflectionFunction($callback))->getParameters();
        $takes = [];
        foreach ([2, 3] as $arguments) {
            $parameter = $parameters[$arguments - 1] ?? null;
            $last = $parameters === [] ? null : $parameters[count($parameters) - 1];
            if ($parameter === null && $last !== null && $last->isVariadic()) {
                $parameter = $last;
            }
            $takes[$arguments] = $parameter !== null && (!$parameter->isOptional() || self::acceptsInt($parameter->getType()));
        }

        return $takes;
    }

    private static function acceptsInt(?ReflectionType $type): bool
    {
        if (!$type instanceof ReflectionType) {
            return true;
        }
        foreach ($type instanceof ReflectionUnionType ? $type->getTypes() : [$type] as $member) {
            if ($member instanceof ReflectionNamedType && in_array($member->getName(), ['int', 'float', 'mixed'], true)) {
                return true;
            }
        }

        return false;
    }
}
