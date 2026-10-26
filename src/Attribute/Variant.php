<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Attribute;

enum Variant: string
{
    case Immutable = 'immutable';
    case Mutable = 'mutable';
    case Odm = 'odm';
}
