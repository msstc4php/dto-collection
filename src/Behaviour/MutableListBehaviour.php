<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

/**
 * Everything a mutable list needs; the concrete class adds the element-typed public methods.
 *
 * @template T
 */
trait MutableListBehaviour
{
    /** @use ListStorage<T> */
    use ListStorage;

    /** @use ReadingBehaviour<T> */
    use ReadingBehaviour;

    /** @use SearchingBehaviour<T> */
    use SearchingBehaviour;

    /** @use AggregatingBehaviour<T> */
    use AggregatingBehaviour;

    /** @use SlicingBehaviour<T> */
    use SlicingBehaviour;

    /** @use OrderingBehaviour<T> */
    use OrderingBehaviour;

    /** @use SetOperationsBehaviour<T> */
    use SetOperationsBehaviour;

    /** @use StructuringBehaviour<T> */
    use StructuringBehaviour;

    /** @use MappingBehaviour<T> */
    use MappingBehaviour;

    /** @use MutableBehaviour<T> */
    use MutableBehaviour;
}
