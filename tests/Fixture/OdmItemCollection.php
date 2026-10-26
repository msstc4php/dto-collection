<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture;

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
 * The ODM variant over Item, unmapped: it runs the contract suites, which compare Item instances.
 *
 * @implements Collection<int, Item>
 * @implements MutableList<Item>
 * @implements TypedList<Item>
 *
 * ODM's generated subclass has another constructor but never calls the static factories.
 *
 * @phpstan-consistent-constructor
 */
class OdmItemCollection implements Collection, MutableList, TypedList
{
    /** @use OdmListBehaviour<Item> */
    use OdmListBehaviour;

    /** @var ElementType<Item>|null */
    private static ?ElementType $type = null;

    private static ?Equality $sameness = null;

    /**
     * @param array<Item> $elements
     */
    #[NoDiscard]
    public static function fromArray(array $elements): static
    {
        return new static($elements);
    }

    /**
     * @param iterable<Item> $elements
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
    public static function of(Item ...$elements): static
    {
        return new static($elements);
    }

    #[NoDiscard]
    public function firstOrFail(): Item
    {
        return $this->doFirstOrFail();
    }

    #[NoDiscard]
    public function lastOrFail(): Item
    {
        return $this->doLastOrFail();
    }

    #[NoDiscard]
    public function getOrFail(int $index): Item
    {
        return $this->doGetOrFail($index);
    }

    #[NoDiscard]
    public function at(int $position): ?Item
    {
        return $this->doAt($position);
    }

    #[NoDiscard]
    public function single(): Item
    {
        return $this->doSingle();
    }

    #[NoDiscard]
    public function singleOrNull(): ?Item
    {
        return $this->doSingleOrNull();
    }

    #[NoDiscard]
    public function random(): ?Item
    {
        return $this->doRandom();
    }

    /**
     * @param Closure(Item, int): bool $predicate
     */
    #[NoDiscard]
    public function find(Closure $predicate): ?Item
    {
        return $this->doFind($predicate);
    }

    /**
     * @param Closure(Item, int): bool $predicate
     */
    #[NoDiscard]
    public function findLast(Closure $predicate): ?Item
    {
        return $this->doFindLast($predicate);
    }

    /**
     * @param Closure(Item): mixed $selector
     */
    #[NoDiscard]
    public function minBy(Closure $selector): ?Item
    {
        return $this->doMinBy($selector);
    }

    /**
     * @param Closure(Item): mixed $selector
     */
    #[NoDiscard]
    public function maxBy(Closure $selector): ?Item
    {
        return $this->doMaxBy($selector);
    }

    #[NoDiscard]
    public function containsAll(Item ...$elements): bool
    {
        return $this->doContainsAll(...$elements);
    }

    #[NoDiscard]
    public function containsAny(Item ...$elements): bool
    {
        return $this->doContainsAny(...$elements);
    }

    #[NoDiscard]
    public function positionOf(Item $element): ?int
    {
        return $this->doPositionOf($element);
    }

    public function push(Item ...$elements): void
    {
        $this->doPush(...$elements);
    }

    public function unshift(Item ...$elements): void
    {
        $this->doUnshift(...$elements);
    }

    public function insertAt(int $index, Item $element): void
    {
        $this->doInsertAt($index, $element);
    }

    public function pop(): ?Item
    {
        return $this->doPop();
    }

    public function shift(): ?Item
    {
        return $this->doShift();
    }

    /**
     * @return ElementType<Item>
     */
    protected function elementType(): ElementType
    {
        return self::$type ??= ElementType::ofClass(Item::class);
    }

    protected function equality(): Equality
    {
        return self::$sameness ??= ComparatorEquality::of(Item::class, static fn (Item $left, Item $right): bool => $left->equals($right));
    }
}
