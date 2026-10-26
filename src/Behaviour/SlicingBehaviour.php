<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\Internal\Callback;
use NoDiscard;

/**
 * Queries that keep the element type; each returns a new list.
 *
 * @template T
 */
trait SlicingBehaviour
{
    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    #[NoDiscard]
    public function filter(Closure $predicate): self
    {
        return $this->copyWith($this->kept($predicate, true));
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    #[NoDiscard]
    public function reject(Closure $predicate): self
    {
        return $this->copyWith($this->kept($predicate, false));
    }

    /**
     * @param Closure(T, int): T $mapper its results are checked against the element type
     *
     * @return static
     */
    #[NoDiscard]
    public function transform(Closure $mapper): self
    {
        $mapped = [];
        foreach ($this->elements as $index => $element) {
            $mapped[] = Callback::invoke($mapper, $element, $index);
        }

        return $this->copyWith($this->coerceAll($mapped));
    }

    /**
     * array_slice() semantics: a negative offset counts from the end, a negative length stops that far from the end.
     *
     * No `?int $length = null`: Doctrine ODM's generated subclass would redeclare it implicitly nullable (deprecated in PHP 8.4).
     *
     * @return static
     */
    #[NoDiscard]
    public function sliced(int $offset, int $length = PHP_INT_MAX): self
    {
        return $this->copyWith(array_slice($this->elements, $offset, $length));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function take(int $count): self
    {
        return $this->copyWith(array_slice($this->elements, 0, self::checkedCount('take', $count)));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function takeLast(int $count): self
    {
        $count = self::checkedCount('takeLast', $count);

        return $this->copyWith($count === 0 ? [] : array_slice($this->elements, -$count));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function drop(int $count): self
    {
        return $this->copyWith(array_slice($this->elements, self::checkedCount('drop', $count)));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function dropLast(int $count): self
    {
        $count = self::checkedCount('dropLast', $count);

        return $this->copyWith(array_slice($this->elements, 0, max(0, count($this->elements) - $count)));
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    #[NoDiscard]
    public function takeWhile(Closure $predicate): self
    {
        return $this->copyWith(array_slice($this->elements, 0, $this->leadingMatches($predicate)));
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return static
     */
    #[NoDiscard]
    public function dropWhile(Closure $predicate): self
    {
        return $this->copyWith(array_slice($this->elements, $this->leadingMatches($predicate)));
    }

    /**
     * @param Closure(T, int): bool $predicate
     *
     * @return list<T>
     */
    private function kept(Closure $predicate, bool $keep): array
    {
        $kept = [];
        foreach ($this->elements as $index => $element) {
            if (Callback::invoke($predicate, $element, $index) === $keep) {
                $kept[] = $element;
            }
        }

        return $kept;
    }

    /**
     * @param Closure(T, int): bool $predicate
     */
    private function leadingMatches(Closure $predicate): int
    {
        foreach ($this->elements as $index => $element) {
            if (!Callback::invoke($predicate, $element, $index)) {
                return $index;
            }
        }

        return count($this->elements);
    }

    /**
     * @return non-negative-int
     */
    private static function checkedCount(string $method, int $count): int
    {
        if ($count < 0) {
            throw new InvalidArgument(sprintf('%s() needs a count of 0 or more, %d given', $method, $count));
        }

        return $count;
    }
}
