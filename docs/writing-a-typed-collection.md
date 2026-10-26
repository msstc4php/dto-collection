# Writing a typed collection

A typed collection is a small class: one trait for the behaviour and one-line public methods
that carry the element type. This page is the contract that `dto-collection-generator` emits.
The reference classes are `tests/Fixture/ItemList.php` (immutable),
`tests/Fixture/MutableItemList.php` (mutable) and `tests/Fixture/Odm/PetCollection.php` (ODM).

In the tables, `T` is the element type as a native hint (`Item`, `int`, `?Item`, `Cat|Dog`).
`?T` means `T` made nullable; for a type that already includes `null`, it is `T` itself.

## Skeleton

```php
/**
 * @implements ImmutableList<Item>      (or MutableList<Item>; the ODM variant also Collection<int, Item>)
 * @implements TypedList<Item>
 */
final class ItemList implements ImmutableList, TypedList   // ODM: not final
{
    /** @use ImmutableListBehaviour<Item> */
    use ImmutableListBehaviour;          // MutableListBehaviour, or Doctrine\OdmListBehaviour
    use NumericBehaviour;                // only for int, float or int|float elements
    use TextBehaviour;                   // only for string elements

    /** @var ElementType<Item>|null */
    private static ?ElementType $type = null;

    private static ?Equality $sameness = null;

    protected function elementType(): ElementType
    {
        return self::$type ??= ElementType::ofClass(Item::class);
    }

    protected function equality(): Equality
    {
        return self::$sameness ??= ComparatorEquality::of(Item::class, static fn (Item $left, Item $right): bool => $left->equals($right));
    }
}
```

The constructor comes from `ListStorage`: `__construct(iterable $elements = [])` checks every
element against `elementType()`.

**Equality.** For a class implementing `Equatable`, use `ComparatorEquality::of()` as above.
Otherwise use `new StrictEquality()` (`===`: identity for objects). For a union, register one
comparator per `Equatable` branch with `->with(Other::class, …)`.

## Every variant

Every public method that returns a value is `#[\NoDiscard]`, except
`remove`/`removeElement`/`pop`/`shift`.

| Method | Body |
|---|---|
| `public static function fromArray(array $elements): static` | `return new static($elements);` |
| `public static function fromIterable(iterable $elements): static` | `return new static($elements);` |
| `public static function empty(): static` | `return new static();` |
| `public static function of(T ...$elements): static` | `return new static($elements);` |
| `firstOrFail(): T` | `return $this->doFirstOrFail();` |
| `lastOrFail(): T` | `return $this->doLastOrFail();` |
| `getOrFail(int $index): T` | `return $this->doGetOrFail($index);` |
| `at(int $position): ?T` | `return $this->doAt($position);` |
| `single(): T` | `return $this->doSingle();` |
| `singleOrNull(): ?T` | `return $this->doSingleOrNull();` |
| `random(): ?T` | `return $this->doRandom();` |
| `find(Closure $predicate): ?T` | `return $this->doFind($predicate);` |
| `findLast(Closure $predicate): ?T` | `return $this->doFindLast($predicate);` |
| `minBy(Closure $selector): ?T` | `return $this->doMinBy($selector);` |
| `maxBy(Closure $selector): ?T` | `return $this->doMaxBy($selector);` |
| `containsAll(T ...$elements): bool` | `return $this->doContainsAll(...$elements);` |
| `containsAny(T ...$elements): bool` | `return $this->doContainsAny(...$elements);` |
| `positionOf(T $element): ?int` | `return $this->doPositionOf($element);` |

Give the closures their PHPDoc, for example `@param Closure(Item, int): bool $predicate` and
`@param Closure(Item): mixed $selector`.

## Immutable and mutable variants

These names exist in Doctrine's `Collection` too, so the ODM variant does not declare them
(see below):

| Method | Body |
|---|---|
| `first(): ?T` | `return $this->doFirst();` |
| `last(): ?T` | `return $this->doLast();` |
| `get(int $index): ?T` | `return $this->doGet($index);` |
| `contains(T $element): bool` | `return $this->doContains($element);` |

## Immutable variant only

| Method | Body |
|---|---|
| `with(T ...$elements): self` | `return $this->doWith(...$elements);` |
| `withPrepended(T ...$elements): self` | `return $this->doWithPrepended(...$elements);` |
| `withSet(int $index, T $element): self` | `return $this->doWithSet($index, $element);` |
| `withInsertedAt(int $index, T $element): self` | `return $this->doWithInsertedAt($index, $element);` |
| `withoutElement(T ...$elements): self` | `return $this->doWithoutElement(...$elements);` |
| `withReplaced(T $old, T $new): self` | `return $this->doWithReplaced($old, $new);` |

Methods that return the list declare `self`, never `static` (see odm.md for why). Add
`@return static` in PHPDoc only when the class is not final.

## Mutable and ODM variants

| Method | Body | Variant |
|---|---|---|
| `push(T ...$elements): void` | `$this->doPush(...$elements);` | both |
| `unshift(T ...$elements): void` | `$this->doUnshift(...$elements);` | both |
| `insertAt(int $index, T $element): void` | `$this->doInsertAt($index, $element);` | both |
| `pop(): ?T` | `return $this->doPop();` | both |
| `shift(): ?T` | `return $this->doShift();` | both |
| `set(int $index, T $element): void` | `$this->doSetAt($index, $element);` | mutable only |
| `remove(int $index): ?T` | `return $this->doRemoveAt($index);` | mutable only |
| `removeElement(T $element): bool` | `return $this->doRemoveElement($element);` | mutable only |

## ODM variant

- The class is not final and keeps the inherited constructor.
- It implements `Doctrine\Common\Collections\Collection`, `MutableList` and `TypedList`, and
  uses `Doctrine\OdmListBehaviour`.
- It **declares none of** `first`, `last`, `get`, `contains`, `add`, `set`, `remove`,
  `removeElement`, `filter`, `map`, `toArray`, `count`, `clear`, `slice`, `indexOf`,
  `exists`, `forAll`, `findFirst`, `partition`, `reduce`, `matching`, `getIterator`,
  `offset*`, `key`, `current`, `next`, `containsKey`, `getKeys` or `getValues`.
  `OdmListBehaviour` provides them with `mixed` and runtime checks. A narrowed
  `first(): ?Pet` is a fatal error once Doctrine ODM generates the persistent subclass.
- Mark the class `@phpstan-consistent-constructor` for the static factories.
- Element types must be documents (embedded or referenced) and must not be nullable.
- No parameter of the form `?X $x = null` in the class's own public methods.

## Interfaces

| Interface | Says |
|---|---|
| `ReadableList<T>` | every query; implemented by every variant |
| `ImmutableList<T>` | `without(int ...)`, `withoutIf(Closure)` |
| `MutableList<T>` | the in-place mutators |
| `TypedList<T>` | `fromArray`, `fromIterable`, `empty`: the element type is fixed by the class |

`toMutable()`/`toImmutable()` return the generic `MutableListOf<T>`/`ListOf<T>`. When both
variants of a DTO's list are generated, the generator may override them to return the paired
class (a covariant return).
