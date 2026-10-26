<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\Equality;

/**
 * The single list every behaviour trait works on, plus what a concrete class must tell about its elements.
 *
 * @template T
 */
trait ListStorage
{
    /** @var list<T> */
    private array $elements = [];

    /**
     * @param iterable<T> $elements keys are discarded
     */
    public function __construct(iterable $elements = [])
    {
        $this->elements = $this->coerceAll($elements);
    }

    /**
     * @return ElementType<T>
     */
    abstract protected function elementType(): ElementType;

    abstract protected function equality(): Equality;

    /**
     * @param iterable<mixed> $values
     *
     * @return list<T>
     */
    protected function coerceAll(iterable $values): array
    {
        $type = $this->elementType();
        $elements = [];
        foreach ($values as $value) {
            $elements[] = $type->coerce($value, count($elements));
        }

        return $elements;
    }

    /**
     * A copy of this list holding $elements, which the caller has already validated.
     *
     * @param list<T> $elements
     *
     * @return static
     */
    protected function copyWith(array $elements): self
    {
        $copy = clone $this;
        $copy->elements = $elements;

        return $copy;
    }
}
