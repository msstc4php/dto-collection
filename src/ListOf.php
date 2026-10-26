<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

use MSSTC4PHP\DtoCollection\Behaviour\AggregatingBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\GenericReadingFacade;
use MSSTC4PHP\DtoCollection\Behaviour\ListStorage;
use MSSTC4PHP\DtoCollection\Behaviour\NumericBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\ReadingBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\SearchingBehaviour;
use MSSTC4PHP\DtoCollection\Behaviour\TextBehaviour;
use MSSTC4PHP\DtoCollection\Equality\Equality;
use MSSTC4PHP\DtoCollection\Equality\EqualityResolver;
use NoDiscard;

/**
 * An immutable list of any element type, checked at runtime. Without a generator, this is the list to use.
 *
 * @template T
 *
 * @implements ReadableList<T>
 */
final class ListOf implements ReadableList
{
    /** @use ListStorage<T> */
    use ListStorage;

    /** @use ReadingBehaviour<T> */
    use ReadingBehaviour;

    /** @use SearchingBehaviour<T> */
    use SearchingBehaviour;

    /** @use AggregatingBehaviour<T> */
    use AggregatingBehaviour;

    use NumericBehaviour;
    use TextBehaviour;

    /** @use GenericReadingFacade<T> */
    use GenericReadingFacade;

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
     * @template C of object
     *
     * @param class-string<C> $class
     * @param iterable<C> $elements
     *
     * @return self<C>
     */
    #[NoDiscard]
    public static function of(string $class, iterable $elements = [], ?Equality $equality = null): self
    {
        return self::ofType(ElementType::ofClass($class), $elements, $equality);
    }

    /**
     * @param iterable<int> $elements
     *
     * @return self<int>
     */
    #[NoDiscard]
    public static function integers(iterable $elements = []): self
    {
        return self::ofType(ElementType::integer(), $elements);
    }

    /**
     * @param iterable<string> $elements
     *
     * @return self<string>
     */
    #[NoDiscard]
    public static function strings(iterable $elements = []): self
    {
        return self::ofType(ElementType::string(), $elements);
    }

    /**
     * @param iterable<int|float> $elements ints are widened to float
     *
     * @return self<float>
     */
    #[NoDiscard]
    public static function floats(iterable $elements = []): self
    {
        return self::ofType(ElementType::float(), $elements);
    }

    /**
     * @param iterable<bool> $elements
     *
     * @return self<bool>
     */
    #[NoDiscard]
    public static function booleans(iterable $elements = []): self
    {
        return self::ofType(ElementType::boolean(), $elements);
    }

    /**
     * @template U
     *
     * @param ElementType<U>|string $type a declaration such as 'Cat|Dog|null' is parsed by ElementType::parse()
     * @param iterable<mixed> $elements
     * @param Equality|null $equality defaults to equals() for Equatable classes and === for the rest
     *
     * @return ($type is string ? self<mixed> : self<U>)
     */
    #[NoDiscard]
    public static function ofType(ElementType|string $type, iterable $elements = [], ?Equality $equality = null): self
    {
        $type = is_string($type) ? ElementType::parse($type) : $type;

        return new self($type, $equality ?? EqualityResolver::forType($type), $elements);
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
