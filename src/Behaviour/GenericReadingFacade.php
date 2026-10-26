<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use NoDiscard;

/**
 * Public element-typed reading methods for the generic lists, typed mixed and checked at runtime.
 *
 * @template T
 */
trait GenericReadingFacade
{
    /**
     * @return T|null
     */
    #[NoDiscard]
    public function get(int $index): mixed
    {
        return $this->doGet($index);
    }
}
