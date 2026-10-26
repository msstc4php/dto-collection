<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use ArrayIterator;
use MSSTC4PHP\DtoCollection\Exception\ReadOnlyCollection;
use MSSTC4PHP\DtoCollection\ReadableList;
use NoDiscard;

/**
 * @template T
 */
trait ReadingBehaviour
{
    /**
     * @return non-negative-int
     */
    #[NoDiscard]
    public function count(): int
    {
        return count($this->elements);
    }

    #[NoDiscard]
    public function isEmpty(): bool
    {
        return $this->elements === [];
    }

    #[NoDiscard]
    public function isNotEmpty(): bool
    {
        return $this->elements !== [];
    }

    /**
     * @return list<T>
     */
    #[NoDiscard]
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * @return list<T>
     */
    #[NoDiscard]
    public function jsonSerialize(): array
    {
        return $this->elements;
    }

    /**
     * @return ArrayIterator<int, T>
     */
    #[NoDiscard]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    #[NoDiscard]
    public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && $offset >= 0 && $offset < count($this->elements);
    }

    /**
     * @return T|null
     */
    #[NoDiscard]
    public function offsetGet(mixed $offset): mixed
    {
        return is_int($offset) ? $this->doGet($offset) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ReadOnlyCollection(sprintf('%s is read-only; use its with*() methods', static::class));
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new ReadOnlyCollection(sprintf('%s is read-only; use its without*() methods', static::class));
    }

    /**
     * @param ReadableList<T> $other
     */
    #[NoDiscard]
    public function equals(ReadableList $other): bool
    {
        $others = $other->toArray();
        if (count($others) !== count($this->elements)) {
            return false;
        }
        $equality = $this->equality();
        foreach ($this->elements as $index => $element) {
            if (!$equality->equals($element, $others[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return T|null
     */
    protected function doGet(int $index): mixed
    {
        return $this->elements[$index] ?? null;
    }
}
