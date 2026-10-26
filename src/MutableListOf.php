<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use MSSTC4PHP\DtoCollection\Behaviour\GenericFactories;
use MSSTC4PHP\DtoCollection\Behaviour\GenericMutableFacade;
use MSSTC4PHP\DtoCollection\Behaviour\GenericReadingFacade;
use MSSTC4PHP\DtoCollection\Behaviour\MutableListBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\NumericBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\TextBehaviour;
use MSSTC4PHP\DtoCollection\Equality\Equality;

/**
 * A mutable list of any element type, checked at runtime.
 *
 * @template T
 *
 * @implements MutableList<T>
 */
final class MutableListOf implements MutableList
{
    /** @use MutableListBehaviour<T> */
    use MutableListBehaviour;

    use NumericBehaviour;
    use TextBehaviour;

    /** @use GenericReadingFacade<T> */
    use GenericReadingFacade;

    /** @use GenericMutableFacade<T> */
    use GenericMutableFacade;

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
