<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Doctrine;

use ArrayIterator;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;

/**
 * The behaviour behind Doctrine's Collection methods; DoctrineCollectionBehaviour only adds the signatures.
 *
 * Keys stay 0..n-1: unlike ArrayCollection, remove() reindexes. "Missing" is null, not false.
 *
 * @template T
 */
trait DoctrineCollectionOperations
{
    /** Doctrine's internal pointer for key(), current() and next(). */
    private int $position = 0;

    protected function doctrineAdd(mixed $element): void
    {
        $this->doPush($this->elementType()->coerce($element, count($this->elements)));
    }

    protected function doctrineContains(mixed $element): bool
    {
        return $this->doContains($this->elementType()->coerce($element, 0));
    }

    protected function doctrineContainsKey(string|int $key): bool
    {
        return is_int($key) && array_key_exists($key, $this->elements);
    }

    /**
     * @return T|null
     */
    protected function doctrineGet(string|int $key): mixed
    {
        return is_int($key) ? $this->doGet($key) : null;
    }

    /**
     * @return list<int>
     */
    protected function doctrineKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * @return T|null
     */
    protected function doctrineFirst(): mixed
    {
        $this->position = 0;

        return $this->doFirst();
    }

    /**
     * @return T|null
     */
    protected function doctrineLast(): mixed
    {
        $this->position = max(0, count($this->elements) - 1);

        return $this->doLast();
    }

    protected function doctrineKey(): ?int
    {
        return array_key_exists($this->position, $this->elements) ? $this->position : null;
    }

    /**
     * @return T|false
     */
    protected function doctrineCurrent(): mixed
    {
        return array_key_exists($this->position, $this->elements) ? $this->elements[$this->position] : false;
    }

    /**
     * @return T|false
     */
    protected function doctrineNext(): mixed
    {
        $this->position++;

        return $this->doctrineCurrent();
    }

    /**
     * @return list<T>
     */
    protected function doctrineSlice(int $offset, ?int $length): array
    {
        return array_slice($this->elements, $offset, $length);
    }

    /**
     * @param Closure(int, T): bool $predicate
     */
    protected function doctrineExists(Closure $predicate): bool
    {
        foreach ($this->elements as $key => $element) {
            if ($predicate($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Closure(int, T): bool $predicate
     */
    protected function doctrineForAll(Closure $predicate): bool
    {
        foreach ($this->elements as $key => $element) {
            if (!$predicate($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Closure(int, T): bool $predicate
     *
     * @return T|null
     */
    protected function doctrineFindFirst(Closure $predicate): mixed
    {
        foreach ($this->elements as $key => $element) {
            if ($predicate($key, $element)) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @param Closure(int, T): bool $predicate
     *
     * @return array{static, static}
     */
    protected function doctrinePartition(Closure $predicate): array
    {
        $matching = [];
        $rest = [];
        foreach ($this->elements as $key => $element) {
            if ($predicate($key, $element)) {
                $matching[] = $element;
            } else {
                $rest[] = $element;
            }
        }

        return [$this->copyWith($matching), $this->copyWith($rest)];
    }

    /**
     * @template U
     *
     * @param Closure(T): U $mapper
     *
     * @return ArrayCollection<int, U>
     */
    protected function doctrineMap(Closure $mapper): ArrayCollection
    {
        return new ArrayCollection(array_map($mapper, $this->elements));
    }

    protected function doctrineIndexOf(mixed $element): int|false
    {
        return $this->doPositionOf($this->elementType()->coerce($element, 0)) ?? false;
    }

    /**
     * @return T|null
     */
    protected function doctrineRemove(string|int $key): mixed
    {
        return is_int($key) ? $this->doRemoveAt($key) : null;
    }

    protected function doctrineRemoveElement(mixed $element): bool
    {
        return $this->doRemoveElement($this->elementType()->coerce($element, 0));
    }

    protected function doctrineSet(string|int $key, mixed $value): void
    {
        if (!is_int($key)) {
            throw new InvalidArgument(sprintf('A list takes integer keys only, "%s" given', $key));
        }
        $this->doSetAt($key, $this->elementType()->coerce($value, $key));
    }

    /**
     * @return static
     */
    protected function doctrineFilter(Closure $predicate): self
    {
        return $this->copyWith($this->kept($predicate, true));
    }

    /**
     * @return ArrayIterator<int, T>
     */
    protected function doctrineIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    protected function doctrineOffsetExists(mixed $offset): bool
    {
        return is_int($offset) && array_key_exists($offset, $this->elements);
    }

    /**
     * @return T|null
     */
    protected function doctrineOffsetGet(mixed $offset): mixed
    {
        return is_int($offset) ? $this->doGet($offset) : null;
    }

    protected function doctrineOffsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->doctrineAdd($value);

            return;
        }
        if (!is_int($offset) && !is_string($offset)) {
            throw new InvalidArgument(sprintf('A list takes integer keys only, %s given', get_debug_type($offset)));
        }
        $this->doctrineSet($offset, $value);
    }

    protected function doctrineOffsetUnset(mixed $offset): void
    {
        if (is_int($offset)) {
            $this->doRemoveAt($offset);
        }
    }

    /**
     * @return static
     */
    protected function doctrineMatching(Criteria $criteria): self
    {
        return $this->copyWith(array_values((new ArrayCollection($this->elements))->matching($criteria)->toArray()));
    }
}
