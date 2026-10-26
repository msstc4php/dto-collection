<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Attribute;

use Attribute;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;

/**
 * Asks dto-collection-generator for a typed collection of the class it marks. Repeat it for several collections.
 *
 * Without elementType the element type is the marked class; with it, a declaration (interface, parent, union)
 * that the marked class belongs to.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class TypedCollection
{
    /**
     * @param string $class fully qualified name of the collection class to generate
     */
    public function __construct(
        public readonly string $class,
        public readonly Variant $variant,
        public readonly bool $nullable = false,
        public readonly ?string $elementType = null,
    ) {
        if (trim($class) === '') {
            throw new InvalidArgument('The collection class name must not be empty');
        }
        if ($elementType !== null) {
            ElementType::parse($elementType);
        }
    }
}
