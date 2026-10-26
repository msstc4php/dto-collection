# Equality

`contains`, `positionOf`, `containsAll`/`containsAny`, `unique`, `diff`, `intersect`, `union`,
`withoutElement`, `withReplaced`, `removeElement` and `equals` all ask the list's `Equality`
whether two elements are the same.

## The rules

| Elements | Same when |
|---|---|
| `int`, `string`, `bool`, `null` | `===` |
| `float` | `===`: `0.1 + 0.2` is not `0.3`. `-0.0` equals `0.0`, and `NAN` equals nothing, not even itself |
| objects of a class implementing `Equatable` | `$left->equals($right)` |
| other objects | identity (`===`) |
| a union `Cat\|Dog` | the rule of each branch; elements of different branches are never equal |

So `ListOf::floats([NAN, NAN])->unique()` keeps both, and `ListOf::floats([NAN])->contains(NAN)`
is `false`.

There is no guessing: a class with an `equals()` method that does not implement `Equatable` is
compared by identity. Someone else's `equals()` with another meaning would otherwise give
wrong answers silently.

## Strategies

- `StrictEquality`: `===`, with the float rules above.
- `ComparatorEquality::of(Item::class, fn (Item $left, Item $right) => $left->sku === $right->sku)`
  compares instances of `Item` with the closure. Chain `->with(Other::class, …)` for further
  classes; the first class that both values belong to decides. Every other value is compared
  strictly.
- `EqualityResolver::forType($elementType)` derives the strategy for the generic lists:
  `ComparatorEquality` over `equals()` for each `Equatable` branch, `StrictEquality` otherwise.

Pass your own strategy to `ListOf::of()`/`ofType()`, or return it from a typed collection's
`equality()`.

## Cost

Scalars and objects compared by identity have a hash key, so set operations on them run in
O(n). Elements compared by a closure or `equals()` have no hash key, so they are compared
pairwise, in O(n·m). For large lists of `Equatable` objects, prefer `uniqueBy()`, `diffBy()` and
`intersectBy()` with a key selector. Those run in O(n).

## Custom strategies

An `Equality` returns a hash key per value. Values it considers equal must get the same key,
or `null` for both. A value with the key `null` is compared pairwise, and only with the other
values whose key is `null`.
