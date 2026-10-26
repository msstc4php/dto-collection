<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

/**
 * Public element-typed mutators for the generic mutable list, typed mixed and checked at runtime.
 *
 * @template T
 */
trait GenericMutableFacade
{
    public function push(mixed ...$elements): void
    {
        $this->doPush(...$this->coerceAll($elements));
    }

    public function unshift(mixed ...$elements): void
    {
        $this->doUnshift(...$this->coerceAll($elements));
    }

    public function set(int $index, mixed $element): void
    {
        $this->doSetAt($index, $this->elementType()->coerce($element, $index));
    }

    public function insertAt(int $index, mixed $element): void
    {
        $this->doInsertAt($index, $this->elementType()->coerce($element, $index));
    }

    public function removeElement(mixed $element): bool
    {
        return $this->doRemoveElement($this->elementType()->coerce($element, 0));
    }

    /**
     * @return T|null
     */
    public function remove(int $index): mixed
    {
        return $this->doRemoveAt($index);
    }

    /**
     * @return T|null
     */
    public function pop(): mixed
    {
        return $this->doPop();
    }

    /**
     * @return T|null
     */
    public function shift(): mixed
    {
        return $this->doShift();
    }
}
