<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use MSSTC4PHP\DtoCollection\Exception\UnsupportedOperation;
use NoDiscard;

/**
 * For lists of string. Other element types throw UnsupportedOperation.
 */
trait TextBehaviour
{
    #[NoDiscard]
    public function join(string $separator = ''): string
    {
        if (!$this->elementType()->isString()) {
            throw new UnsupportedOperation(sprintf('join() needs the element type string; the list holds %s', $this->elementType()->describe()));
        }

        return implode($separator, array_filter($this->elements, is_string(...)));
    }
}
