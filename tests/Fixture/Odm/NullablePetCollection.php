<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture\Odm;

use Closure;
use Doctrine\Common\Collections\Collection;
use MSSTC4PHP\DtoCollection\Doctrine\OdmListBehaviour;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\ComparatorEquality;
use MSSTC4PHP\DtoCollection\Equality\Equality;
use MSSTC4PHP\DtoCollection\MutableList;
use MSSTC4PHP\DtoCollection\TypedList;
use NoDiscard;

/**
 * The ODM variant over Pet|null, to see what ODM does with null elements.
 *
 * @implements Collection<int, Pet|null>
 * @implements MutableList<Pet|null>
 * @implements TypedList<Pet|null>
 *
 * ODM's generated subclass has another constructor but never calls the static factories.
 *
 * @phpstan-consistent-constructor
 */
class NullablePetCollection implements Collection, MutableList, TypedList
{
    /** @use OdmListBehaviour<Pet|null> */
    use OdmListBehaviour;

    /** @var ElementType<Pet|null>|null */
    private static ?ElementType $type = null;

    private static ?Equality $sameness = null;

    /**
     * @param array<Pet|null> $elements
     */
    #[NoDiscard]
    public static function fromArray(array $elements): static
    {
        return new static($elements);
    }

    /**
     * @param iterable<Pet|null> $elements
     */
    #[NoDiscard]
    public static function fromIterable(iterable $elements): static
    {
        return new static($elements);
    }

    #[NoDiscard]
    public static function empty(): static
    {
        return new static();
    }

    #[NoDiscard]
    public static function of(?Pet ...$elements): static
    {
        return new static($elements);
    }

    #[NoDiscard]
    public function firstOrFail(): ?Pet
    {
        return $this->doFirstOrFail();
    }

    #[NoDiscard]
    public function lastOrFail(): ?Pet
    {
        return $this->doLastOrFail();
    }

    #[NoDiscard]
    public function getOrFail(int $index): ?Pet
    {
        return $this->doGetOrFail($index);
    }

    #[NoDiscard]
    public function at(int $position): ?Pet
    {
        return $this->doAt($position);
    }

    #[NoDiscard]
    public function single(): ?Pet
    {
        return $this->doSingle();
    }

    #[NoDiscard]
    public function singleOrNull(): ?Pet
    {
        return $this->doSingleOrNull();
    }

    #[NoDiscard]
    public function random(): ?Pet
    {
        return $this->doRandom();
    }

    /**
     * @param Closure(Pet|null, int): bool $predicate
     */
    #[NoDiscard]
    public function find(Closure $predicate): ?Pet
    {
        return $this->doFind($predicate);
    }

    /**
     * @param Closure(Pet|null, int): bool $predicate
     */
    #[NoDiscard]
    public function findLast(Closure $predicate): ?Pet
    {
        return $this->doFindLast($predicate);
    }

    /**
     * @param Closure(Pet|null): mixed $selector
     */
    #[NoDiscard]
    public function minBy(Closure $selector): ?Pet
    {
        return $this->doMinBy($selector);
    }

    /**
     * @param Closure(Pet|null): mixed $selector
     */
    #[NoDiscard]
    public function maxBy(Closure $selector): ?Pet
    {
        return $this->doMaxBy($selector);
    }

    #[NoDiscard]
    public function containsAll(?Pet ...$elements): bool
    {
        return $this->doContainsAll(...$elements);
    }

    #[NoDiscard]
    public function containsAny(?Pet ...$elements): bool
    {
        return $this->doContainsAny(...$elements);
    }

    #[NoDiscard]
    public function positionOf(?Pet $element): ?int
    {
        return $this->doPositionOf($element);
    }

    public function push(?Pet ...$elements): void
    {
        $this->doPush(...$elements);
    }

    public function unshift(?Pet ...$elements): void
    {
        $this->doUnshift(...$elements);
    }

    public function insertAt(int $index, ?Pet $element): void
    {
        $this->doInsertAt($index, $element);
    }

    public function pop(): ?Pet
    {
        return $this->doPop();
    }

    public function shift(): ?Pet
    {
        return $this->doShift();
    }

    /**
     * @return ElementType<Pet|null>
     */
    protected function elementType(): ElementType
    {
        return self::$type ??= ElementType::ofClass(Pet::class)->nullable();
    }

    protected function equality(): Equality
    {
        return self::$sameness ??= ComparatorEquality::of(Pet::class, static fn (Pet $left, Pet $right): bool => $left->equals($right));
    }
}
