<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\Exception\InvalidElementType;
use MSSTC4PHP\DtoCollection\Internal\ElementTypeParser;
use ReflectionClass;

/**
 * The type every element of a list must have: a scalar, a class, an intersection of classes, a union of those, optionally nullable.
 *
 * @template-covariant T the PHP type of an accepted element
 *
 * @phpstan-type ScalarBranch 'int'|'string'|'float'|'bool'
 * @phpstan-type Branch ScalarBranch|non-empty-list<class-string>
 */
final class ElementType
{
    /**
     * @param non-empty-list<Branch> $branches
     */
    private function __construct(
        private readonly array $branches,
        private readonly bool $nullable,
    ) {
    }

    /**
     * @return self<int>
     */
    public static function integer(): self
    {
        return new self(['int'], false);
    }

    /**
     * @return self<string>
     */
    public static function string(): self
    {
        return new self(['string'], false);
    }

    /**
     * @return self<float>
     */
    public static function float(): self
    {
        return new self(['float'], false);
    }

    /**
     * @return self<bool>
     */
    public static function boolean(): self
    {
        return new self(['bool'], false);
    }

    /**
     * @template C of object
     *
     * @param class-string<C> $class
     *
     * @return self<C>
     */
    public static function ofClass(string $class): self
    {
        return new self([[self::className($class)]], false);
    }

    /**
     * @param class-string ...$classes
     *
     * @return self<object>
     */
    public static function intersection(string ...$classes): self
    {
        $names = array_map(self::className(...), array_values($classes));
        if (count($names) < 2) {
            throw new InvalidElementType('An intersection needs at least two classes');
        }
        if (count(array_unique($names)) !== count($names)) {
            throw new InvalidElementType(sprintf('An intersection repeats a class: %s', implode('&', $names)));
        }

        return new self([$names], false);
    }

    /**
     * @template U
     *
     * @param self<U> ...$types
     *
     * @return self<U>
     */
    public static function union(self ...$types): self
    {
        $branches = [];
        $nullable = false;
        foreach ($types as $type) {
            foreach ($type->branches as $branch) {
                $key = self::describeBranch($branch);
                if (isset($branches[$key])) {
                    throw new InvalidElementType(sprintf('A union repeats the type %s', $key));
                }
                $branches[$key] = $branch;
            }
            $nullable = $nullable || $type->nullable;
        }
        if ($branches === []) {
            throw new InvalidElementType('A union needs at least one type');
        }

        return new self(array_values($branches), $nullable);
    }

    /**
     * Parses a PHP-like declaration: `int`, `?Foo`, `Cat|Dog|null`, `Countable&Traversable`.
     *
     * @return self<mixed>
     */
    public static function parse(string $declaration): self
    {
        return ElementTypeParser::parse($declaration);
    }

    /**
     * @return self<T|null>
     */
    public function nullable(): self
    {
        return new self($this->branches, true);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function accepts(mixed $value): bool
    {
        if ($value === null) {
            return $this->nullable;
        }

        return $this->matchesBranch($value) || $this->widensToFloat($value);
    }

    /**
     * Returns the value as an element, widening an int to float the way a native float parameter does under strict_types.
     *
     * @return T
     *
     * @throws ElementTypeMismatch
     */
    public function coerce(mixed $value, int $index): mixed
    {
        if ($value === null ? $this->nullable : $this->matchesBranch($value)) {
            return $value;
        }
        if (is_int($value) && $this->widensToFloat($value)) {
            return $this->widen($value);
        }

        throw ElementTypeMismatch::at($index, $this, $value);
    }

    public function isNumeric(): bool
    {
        if ($this->nullable) {
            return false;
        }
        foreach ($this->branches as $branch) {
            if ($branch !== 'int' && $branch !== 'float') {
                return false;
            }
        }

        return true;
    }

    public function isString(): bool
    {
        return !$this->nullable && $this->branches === ['string'];
    }

    /**
     * @return list<non-empty-list<class-string>>
     */
    public function objectBranches(): array
    {
        return array_values(array_filter($this->branches, is_array(...)));
    }

    /**
     * @return non-empty-string
     */
    public function describe(): string
    {
        $parts = array_map(self::describeBranch(...), $this->branches);
        if ($this->nullable) {
            $parts[] = 'null';
        }

        return implode('|', $parts);
    }

    /**
     * @param self<mixed> $other
     */
    public function equals(self $other): bool
    {
        return $this->nullable === $other->nullable && $this->sortedBranches() === $other->sortedBranches();
    }

    private function matchesBranch(mixed $value): bool
    {
        foreach ($this->branches as $branch) {
            $matches = match ($branch) {
                'int' => is_int($value),
                'string' => is_string($value),
                'float' => is_float($value),
                'bool' => is_bool($value),
                default => $this->isInstanceOfAll($value, $branch),
            };
            if ($matches) {
                return true;
            }
        }

        return false;
    }

    private function widensToFloat(mixed $value): bool
    {
        return is_int($value) && in_array('float', $this->branches, true) && !in_array('int', $this->branches, true);
    }

    /**
     * An int widened to float is a T exactly when widensToFloat() holds; mixed lets coerce() return it as T.
     */
    private function widen(int $value): mixed
    {
        return (float) $value;
    }

    /**
     * @return list<non-empty-string>
     */
    private function sortedBranches(): array
    {
        $described = array_map(self::describeBranch(...), $this->branches);
        sort($described);

        return $described;
    }

    /**
     * @param non-empty-list<class-string> $classes
     */
    private function isInstanceOfAll(mixed $value, array $classes): bool
    {
        foreach ($classes as $class) {
            if (!$value instanceof $class) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ScalarBranch|non-empty-list<class-string> $branch
     *
     * @return non-empty-string
     */
    private static function describeBranch(string|array $branch): string
    {
        return is_string($branch) ? $branch : implode('&', $branch);
    }

    /**
     * @return class-string
     */
    private static function className(string $class): string
    {
        $name = ltrim($class, '\\');
        if (!class_exists($name) && !interface_exists($name)) {
            throw new InvalidElementType(sprintf('Class or interface "%s" does not exist', $name));
        }

        return (new ReflectionClass($name))->getName();
    }
}
