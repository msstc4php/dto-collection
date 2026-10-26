<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use MSSTC4PHP\DtoCollection\Behaviour\GenericFactories;
use MSSTC4PHP\DtoCollection\Behaviour\GenericImmutableFacade;
use MSSTC4PHP\DtoCollection\Behaviour\GenericReadingFacade;
use MSSTC4PHP\DtoCollection\Behaviour\ImmutableListBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\NumericBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\TextBehaviour;
use MSSTC4PHP\DtoCollection\Equality\Equality;

/**
 * An immutable list of any element type, checked at runtime. Without a generator, this is the list to use.
 *
 * @template T
 *
 * @implements ImmutableList<T>
 */
final class ListOf implements ImmutableList
{
    /** @use ImmutableListBehaviour<T> */
    use ImmutableListBehaviour;

    use NumericBehaviour;
    use TextBehaviour;

    /** @use GenericReadingFacade<T> */
    use GenericReadingFacade;

    /** @use GenericImmutableFacade<T> */
    use GenericImmutableFacade;

    use GenericFactories;

    /**
     * @param ElementType<T> $type
     * @param iterable<mixed> $elements
     */
    private function __construct(
        private readonly ElementType $type,
        private readonly Equality $equality,
        iterable $elements,
    ) {
        $this->elements = $this->coerceAll($elements);
    }

    /**
     * @return ElementType<T>
     */
    protected function elementType(): ElementType
    {
        return $this->type;
    }

    protected function equality(): Equality
    {
        return $this->equality;
    }
}
