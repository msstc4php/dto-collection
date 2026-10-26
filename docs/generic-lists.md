# Generic lists

`ListOf` is immutable and `MutableListOf` is mutable. Both take any element type and check it
at runtime.

## Factories

```php
ListOf::of(User::class, $users);           // ListOf<User>
ListOf::integers([1, 2]);                  // ListOf<int>
ListOf::strings(['a']);                    // ListOf<string>
ListOf::floats([1, 2.5]);                  // ListOf<float>: ints are widened, so [1.0, 2.5]
ListOf::booleans([true]);
ListOf::ofType('Cat|Dog|null', $pets);     // a type declaration, see below
ListOf::ofType(ElementType::ofClass(Cat::class)->nullable(), $pets);
```

`MutableListOf` has the same factories. Each takes any `iterable` and discards its keys:
`ListOf::integers([5 => 1, 'a' => 2])` holds `[1, 2]`.

`of()` and `ofType()` take an optional `Equality`. Without one, `Equatable` classes are compared
with `equals()` and everything else with `===` (see [equality.md](equality.md)).

## Element types

`ElementType` describes what a list accepts:

| Factory | Accepts |
|---|---|
| `ElementType::integer()`, `string()`, `float()`, `boolean()` | that scalar; `float` also takes an int and widens it, like a native `float` parameter under `strict_types` |
| `ElementType::ofClass(Foo::class)` | `Foo` and its subtypes |
| `ElementType::intersection(A::class, B::class)` | objects that are both; PHPStan reads it as `ElementType<object>` |
| `ElementType::ofClass(A::class)->andInstanceOf(B::class)` | the same, read by PHPStan as `ElementType<A&B>`; chain it for more classes |
| `ElementType::union($a, $b)` | either; an int stays an int when `int` is one of the branches |
| `->nullable()` | also `null` |
| `ElementType::parse('?int')`, `'Cat\|Dog\|null'`, `'Countable&Traversable'` | the same, from a declaration |

`parse()` rejects `mixed`, `array`, `callable`, `iterable`, `object`, `self`, `static`,
`void`, `never`, `false`, `true` and a lone `null`. It also rejects repeated branches,
unknown classes, and DNF types such as `(A&B)|null`, which need PHP 8.2.

An element of the wrong type throws `ElementTypeMismatch` with its position:
`Element #1 must be int, string given`.

## Methods that take an element

The generic lists type these methods as `mixed` and check their arguments the same way:
`contains`, `containsAll`, `containsAny`, `positionOf`, `with`, `withPrepended`, `withSet`,
`withInsertedAt`, `withoutElement`, `withReplaced` (`ListOf`), and `push`, `unshift`, `set`,
`insertAt`, `removeElement` (`MutableListOf`). `ListOf::integers([1])->contains('1')` throws
instead of answering `false`.

## Numbers and strings

Both generic lists have `sum()`, `average()`, `min()` and `max()`. They work when every branch
of the element type is `int` or `float` and the type is not nullable, and throw
`UnsupportedOperation` otherwise. `join()` needs the element type `string`.
