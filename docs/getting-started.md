# Getting started

## 1. A generic list, no class of your own

`ListOf` (immutable) and `MutableListOf` (mutable) take any element type and check every
element at runtime:

```php
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\MutableListOf;

$tags = ListOf::strings(['php', 'ddd']);
$more = $tags->with('odm');              // a new list; $tags is unchanged

$queue = MutableListOf::of(Job::class);
$queue->push(new Job('import'));         // changes $queue in place
$next = $queue->shift();                 // ?Job (PHPStan sees Job|null)
```

PHPStan knows the element type through generics (`ListOf<Job>`). PHP sees `mixed`.

## 2. A typed list class

For the element type to be a native type hint, write a small class:

```php
use MSSTC4PHP\DtoCollection\Behaviour\ImmutableListBehaviour;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Equality\Equality;
use MSSTC4PHP\DtoCollection\Equality\StrictEquality;
use MSSTC4PHP\DtoCollection\ImmutableList;
use MSSTC4PHP\DtoCollection\TypedList;

/**
 * @implements ImmutableList<Job>
 * @implements TypedList<Job>
 */
final class Jobs implements ImmutableList, TypedList
{
    /** @use ImmutableListBehaviour<Job> */
    use ImmutableListBehaviour;

    public static function of(Job ...$jobs): static { return new static($jobs); }
    public function first(): ?Job { return $this->doFirst(); }
    public function with(Job ...$jobs): self { return $this->doWith(...$jobs); }
    // … one line per element-typed method

    protected function elementType(): ElementType { return ElementType::ofClass(Job::class); }
    protected function equality(): Equality { return new StrictEquality(); }
}
```

[Writing a typed collection](writing-a-typed-collection.md) lists every method the class
declares. `dto-collection-generator` writes these classes for you.

## 3. Choosing a variant

| You need | Use |
|---|---|
| Values that never change after creation, as in DTOs | immutable: `ListOf`, `ImmutableListBehaviour` |
| A working list you fill and sort in place | mutable: `MutableListOf`, `MutableListBehaviour` |
| A Doctrine ODM document field | ODM: `OdmListBehaviour`, see [odm.md](odm.md) |

Convert between them with `toMutable()` and `toImmutable()`.

## 4. Errors

Every exception implements `MSSTC4PHP\DtoCollection\Exception\CollectionException` and
extends the matching SPL exception:

| Exception | When |
|---|---|
| `ElementTypeMismatch` (`InvalidArgumentException`) | an element of the wrong type, at runtime |
| `IndexOutOfRange` (`OutOfRangeException`) | `getOrFail(9)`, `withSet(9, …)`, `swap(0, 9)` |
| `EmptyCollection` (`UnderflowException`) | `firstOrFail()` on an empty list |
| `NotExactlyOneElement` (`LengthException`) | `single()` on zero or several elements |
| `UnsupportedOperation` (`LogicException`) | `sum()` on a list that is not numeric, `join()` on one that is not of strings |
| `ReadOnlyCollection` (`LogicException`) | `$list[0] = $x` on a non-ODM list |
| `InvalidArgument` (`InvalidArgumentException`) | `take(-1)`, `chunk(0)`, a string key on the ODM variant |
| `InvalidElementType` (`InvalidArgumentException`) | `ElementType::parse('mixed')` |
