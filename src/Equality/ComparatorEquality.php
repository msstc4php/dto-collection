<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Equality;

use Closure;

/**
 * Compares instances of the registered classes with their closure; everything else strictly.
 */
final class ComparatorEquality implements Equality
{
    private readonly StrictEquality $strict;

    /**
     * @param array<class-string, Closure(never, never): bool> $comparators the first class both values belong to decides
     */
    private function __construct(private readonly array $comparators)
    {
        $this->strict = new StrictEquality();
    }

    /**
     * @template C of object
     *
     * @param class-string<C> $class
     * @param Closure(C, C): bool $comparator
     */
    public static function of(string $class, Closure $comparator): self
    {
        return new self([$class => $comparator]);
    }

    /**
     * A copy that also compares instances of $class, after the classes registered so far.
     *
     * @template C of object
     *
     * @param class-string<C> $class
     * @param Closure(C, C): bool $comparator
     */
    public function with(string $class, Closure $comparator): self
    {
        return new self([...$this->comparators, $class => $comparator]);
    }

    public function equals(mixed $left, mixed $right): bool
    {
        foreach ($this->comparators as $class => $comparator) {
            if ($left instanceof $class && $right instanceof $class) {
                // Reason: the comparator of class C only receives C instances (checked above); PHPStan has no existential
                // type to express array<class-string<C>, Closure(C, C): bool>, so it is stored as Closure(never, never).
                return $comparator($left, $right); // @phpstan-ignore argument.type, argument.type
            }
        }

        return $this->strict->equals($left, $right);
    }

    public function hashKey(mixed $value): ?string
    {
        foreach ($this->comparators as $class => $comparator) {
            if ($value instanceof $class) {
                return null;
            }
        }

        return $this->strict->hashKey($value);
    }
}
