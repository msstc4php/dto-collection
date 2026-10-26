<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Internal;

use Closure;
use ReflectionFunction;
use WeakMap;

/**
 * Calls user callbacks with the element index only when they ask for it.
 *
 * @internal
 */
final class Callback
{
    /** @var WeakMap<Closure, array{int, int, bool}>|null */
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
     * Whether $callback can take $arguments positional arguments without a TypeError on the extra ones.
     *
     * Internal functions are asked for required parameters only: passing an index into trim()'s optional
     * $characters would fail under strict_types.
     *
     * @param Closure(never, never, never): mixed $callback
     * @param positive-int $arguments
     */
    public static function takes(Closure $callback, int $arguments): bool
    {
        self::$signatures ??= new WeakMap();
        [$parameters, $required, $userDefined] = self::$signatures[$callback] ??= self::signature($callback);

        return $userDefined ? $parameters >= $arguments : $required >= $arguments;
    }

    /**
     * @return array{int, int, bool}
     */
    private static function signature(Closure $callback): array
    {
        $function = new ReflectionFunction($callback);
        // A variadic closure takes any number of arguments.
        $parameters = $function->isVariadic() && $function->isUserDefined() ? PHP_INT_MAX : $function->getNumberOfParameters();

        return [$parameters, $function->getNumberOfRequiredParameters(), $function->isUserDefined()];
    }
}
