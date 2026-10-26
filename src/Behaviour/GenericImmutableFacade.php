<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use NoDiscard;

/**
 * Public element-typed with*() methods for the generic immutable list, typed mixed and checked at runtime.
 *
 * @template T
 */
trait GenericImmutableFacade
{
    /**
     * @return static
     */
    #[NoDiscard]
    public function with(mixed ...$elements): self
    {
        return $this->doWith(...$this->coerceAll($elements));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function withPrepended(mixed ...$elements): self
    {
        return $this->doWithPrepended(...$this->coerceAll($elements));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function withSet(int $index, mixed $element): self
    {
        return $this->doWithSet($index, $this->elementType()->coerce($element, $index));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function withInsertedAt(int $index, mixed $element): self
    {
        return $this->doWithInsertedAt($index, $this->elementType()->coerce($element, $index));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function withoutElement(mixed ...$elements): self
    {
        return $this->doWithoutElement(...$this->coerceAll($elements));
    }

    /**
     * @return static
     */
    #[NoDiscard]
    public function withReplaced(mixed $old, mixed $new): self
    {
        return $this->doWithReplaced($this->elementType()->coerce($old, 0), $this->elementType()->coerce($new, 1));
    }
}
