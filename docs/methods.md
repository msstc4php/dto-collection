# Methods

`T` is the element type. Callbacks take `(T $element, int $index)`. The index is passed when
the second parameter is required, or optional with a type that takes an int (none, `int`,
`float`, `mixed`). So `filter(is_string(...))`, `transform(trim(...))` and a helper such as
`normalise(string $text, bool $lowerCase = false)` all work. Selectors
(`…By`, `keyBy`, `countBy`, `groupBy`) take `(T $element)`. A key selector (`groupBy`, `keyBy`,
`countBy`, `uniqueBy`, `diffBy`, `intersectBy`) must return an int, a string or a backed enum,
whose value is then used. Anything else throws `InvalidArgument`: PHP would silently truncate
a float key. "Missing" is `null`; each
`…OrFail()` throws instead. Every query returns a new list of the same class.

## Reading (every variant)

| Group | Methods |
|---|---|
| size | `count()`, `isEmpty()`, `isNotEmpty()` |
| access | `first()`, `last()`, `firstOrFail()`, `lastOrFail()`, `get(int)`, `getOrFail(int)`, `at(int)` (a negative position counts from the end), `single()` (exactly one element, otherwise `NotExactlyOneElement`), `singleOrNull()`, `random()` |
| search | `contains(T)`, `containsAll(T ...)` (true with no argument), `containsAny(T ...)` (false with no argument), `positionOf(T): ?int`, `find()`, `findLast()`, `findPosition(): ?int` |
| predicates | `any()`, `all()` (true on an empty list), `none()`, `countWhere(): int`, `countBy(): array<key, count>` (keys in first-seen order) |
| aggregates | `reduce(Closure $reducer, $initial = null)` (Doctrine's signature; a reducer declaring a third parameter gets the index), `sumBy()` (0 on empty), `averageBy()` (null on empty), `minBy()`, `maxBy()` (the first of equal keys wins). Keys are compared with `<=>`, so a `NAN` key makes `minBy`, `maxBy` and `sortedBy` depend on the order of the elements |
| numbers, strings | `sum()`, `average()`, `min()`, `max()`, needing `int`, `float` or `int\|float` elements (`NumericBehaviour`); `join(string $separator = '')`, needing `string` elements (`TextBehaviour`) |
| same element type | `filter()`, `reject()`, `transform(Closure(T, int): T)` (the results are checked), `sliced(int $offset, int $length = PHP_INT_MAX)` (`array_slice()` semantics), `take()`, `takeLast()`, `drop()`, `dropLast()` (a negative count throws), `takeWhile()`, `dropWhile()` |
| order | `sorted(Closure(T, T): int)`, `sortedBy(Closure(T): mixed, bool $descending = false)` (stable). To sort by several keys, return an array: `sortedBy(fn ($u) => [$u->lastName, $u->firstName])`, or chain calls, least significant key first. Then `reversed()`, `shuffled()` |
| sets | `unique()`, `uniqueBy()`, `diff(iterable ...)`, `diffBy()`, `intersect(iterable ...)`, `intersectBy()`, `union(iterable ...)`, `merge(iterable ...)`; see [equality.md](equality.md) |
| structure | `split(): array{static, static}` (matching first), `groupBy(): array<key, static>`, `chunk(int): list<static>`, `windowed(int $size, int $step = 1, bool $partial = false)`, `chunkWhile(Closure(T $previous, T $current): bool)`, `keyBy(): array<key, T>` (the last element wins a key) |
| other types | `mapTo(ElementType\|string $type, Closure)`, `flatMapTo(...)`, which return a `ListOf` and check the results against `$type`; `mapToArray(): list` (unchecked); `mapInto(TypedList::class, Closure)` |
| other | `each(Closure)` (side effects; a callback returning exactly `false` stops; returns the list itself), `pipe(Closure ...$steps)` |
| export | `toArray(): list<T>`, `jsonSerialize()`, `getIterator()` (over a snapshot), `equals(ReadableList)` (same length, pairwise equal), `toMutable(): MutableListOf<T>`, `toImmutable(): ListOf<T>` |

`ArrayAccess` reads like `get()`: `$list[0]`, `isset($list[0])`. Writing throws
`ReadOnlyCollection`, except on the ODM variant.

Lists are not meant for `serialize()`. `unserialize()` bypasses the element type check, and a
`ComparatorEquality` holds closures, which cannot be serialized. Store `toArray()` and rebuild
with `fromArray()`.

## Immutable variant

`with(T ...)`, `withPrepended(T ...)`, `withSet(int, T)` (an existing index, or the length to
append), `withInsertedAt(int, T)`, `without(int ...)`, `withoutElement(T ...)` (each argument
removes the first equal element), `withoutIf(Closure)`, `withReplaced(T $old, T $new)` (every
equal element).

## Mutable and ODM variants

These change the list in place and return `void` unless noted: `push(T ...)`, `unshift(T ...)`,
`set(int, T)`, `insertAt(int, T)`, `remove(int): ?T` (null when the index is missing),
`removeElement(T): bool` (the first equal element), `removeIf(Closure): int` (how many were
removed), `pop(): ?T`, `shift(): ?T`, `clear()`, `sort(Closure)`, `sortBy(Closure, bool $descending = false)`,
`reverse()`, `swap(int, int)`, `move(int $from, int $to)`.

A callback passed to a mutator (`removeIf`, `sort`, `sortBy`) must not change the list: the
mutator writes its result over those changes.

## ODM variant only

Doctrine's `Collection`: `add`, `containsKey`, `getKeys`, `getValues`, `slice`, `exists`,
`forAll`, `findFirst`, `partition`, `map`, `indexOf`, `key`, `current`, `next`, `matching`. See
[odm.md](odm.md) for where they differ from `ArrayCollection`.
