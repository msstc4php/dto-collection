<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture\Odm;

use Closure;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use MSSTC4PHP\DtoCollection\Doctrine\OdmListBehaviour;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\Equality;
use MSSTC4PHP\DtoCollection\Equality\StrictEquality;
use MSSTC4PHP\DtoCollection\MutableList;
use MSSTC4PHP\DtoCollection\TypedList;
use NoDiscard;

/**
 * The ODM variant over the referenced document Owner, compared by identity (ODM's identity map).
 *
 * @implements Collection<int, Owner>
 * @implements Selectable<int, Owner>
 * @implements MutableList<Owner>
 * @implements TypedList<Owner>
 *
 * Selectable is explicit because doctrine/collections 2's Collection does not extend it, and ODM's matching()
 * checks it. ODM's generated subclass has another constructor but never calls the static factories.
 *
 * @phpstan-consistent-constructor
 */
class OwnerCollection implements Collection, Selectable, MutableList, TypedList
{
    /** @use OdmListBehaviour<Owner> */
    use OdmListBehaviour;

    /** @var ElementType<Owner>|null */
    private static ?ElementType $type = null;

    private static ?Equality $sameness = null;

    /**
     * @param array<Owner> $elements
     */
    #[NoDiscard]
    public static function fromArray(array $elements): static
    {
        return new static($elements);
    }

    /**
     * @param iterable<Owner> $elements
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
    public static function of(Owner ...$elements): static
    {
        return new static($elements);
    }

    #[NoDiscard]
    public function firstOrFail(): Owner
    {
        return $this->doFirstOrFail();
    }

    #[NoDiscard]
    public function lastOrFail(): Owner
    {
        return $this->doLastOrFail();
    }

    #[NoDiscard]
    public function getOrFail(int $index): Owner
    {
        return $this->doGetOrFail($index);
    }

    #[NoDiscard]
    public function at(int $position): ?Owner
    {
        return $this->doAt($position);
    }

    #[NoDiscard]
    public function single(): Owner
    {
        return $this->doSingle();
    }

    #[NoDiscard]
    public function singleOrNull(): ?Owner
    {
        return $this->doSingleOrNull();
    }

    #[NoDiscard]
    public function random(): ?Owner
    {
        return $this->doRandom();
    }

    /**
     * @param Closure(Owner, int): bool $predicate
     */
    #[NoDiscard]
    public function find(Closure $predicate): ?Owner
    {
        return $this->doFind($predicate);
    }

    /**
     * @param Closure(Owner, int): bool $predicate
     */
    #[NoDiscard]
    public function findLast(Closure $predicate): ?Owner
    {
        return $this->doFindLast($predicate);
    }

    /**
     * @param Closure(Owner): mixed $selector
     */
    #[NoDiscard]
    public function minBy(Closure $selector): ?Owner
    {
        return $this->doMinBy($selector);
    }

    /**
     * @param Closure(Owner): mixed $selector
     */
    #[NoDiscard]
    public function maxBy(Closure $selector): ?Owner
    {
        return $this->doMaxBy($selector);
    }

    #[NoDiscard]
    public function containsAll(Owner ...$elements): bool
    {
        return $this->doContainsAll(...$elements);
    }

    #[NoDiscard]
    public function containsAny(Owner ...$elements): bool
    {
        return $this->doContainsAny(...$elements);
    }

    #[NoDiscard]
    public function positionOf(Owner $element): ?int
    {
        return $this->doPositionOf($element);
    }

    public function push(Owner ...$elements): void
    {
        $this->doPush(...$elements);
    }

    public function unshift(Owner ...$elements): void
    {
        $this->doUnshift(...$elements);
    }

    public function insertAt(int $index, Owner $element): void
    {
        $this->doInsertAt($index, $element);
    }

    public function pop(): ?Owner
    {
        return $this->doPop();
    }

    public function shift(): ?Owner
    {
        return $this->doShift();
    }

    /**
     * @return ElementType<Owner>
     */
    protected function elementType(): ElementType
    {
        return self::$type ??= ElementType::ofClass(Owner::class);
    }

    protected function equality(): Equality
    {
        return self::$sameness ??= new StrictEquality();
    }
}
