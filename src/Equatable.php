<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

/**
 * Lets a list compare elements by value; without it, objects are compared by identity.
 */
interface Equatable
{
    public function equals(object $other): bool;
}
