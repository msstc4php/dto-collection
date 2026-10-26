# Domain model

- **List invariant:** keys `0..n-1` after every operation. `set`/`withSet`/`insertAt` accept an
  index in `[0, count]` (count = append).
- **ElementType** (`@template-covariant T`): branches = scalar name or non-empty list of
  classes (intersection) + `nullable` flag. `coerce()` widens int→float only when a float
  branch exists and no int branch. `isNumeric()`/`isString()` are false for nullable types.
  `describe()` keeps declaration order, `null` last; `equals()` is order-insensitive, also
  inside intersections. `intersection()` is PHPDoc-typed `self<object>` (variadic class-strings
  cannot be intersected in PHPDoc); `andInstanceOf()` (1.1) is `self<T&C>`, so the generator
  uses `ofClass(A)->andInstanceOf(B)`. It accepts only a single non-nullable object branch and
  validates through `intersection()`. `tests/Types/*.php` are `assertType()` files: analysed by
  PHPStan, never run.
- **Parser** (`Internal\ElementTypeParser`): `?T` only as the whole declaration; `|` with `&` or
  any `(` → DNF → rejected (PHP 8.2); scalars case-insensitive; messages are pinned by tests.
- **Equality:** `StrictEquality` (`===`; float keys normalise -0.0; NAN has no key),
  `ComparatorEquality::of(C, Closure(C, C): bool)->with(...)`, `EqualityResolver` for generic
  lists (Equatable single-class branches only; no `method_exists` guessing).
- **HashSet** (`Internal`): keyed values by key, keyless values pairwise among themselves. It
  stores the values, not only keys (object keys are `spl_object_id`, reused after GC).
- **Callback** (`Internal`): passes the index when the parameter at that position (or a
  trailing variadic) is required, or optional with a type that takes an int (none, int, float,
  mixed, or a union with one of them) — same rule for internal and user functions.
- **SelectorKey** (`Internal`): key selectors must return int|string|BackedEnum (value used);
  anything else → InvalidArgument (PHP would truncate floats / refuse null silently).
- **StrictEquality float keys:** `bin2hex(pack('E', $v + 0.0))` — never var_export(), which
  follows `serialize_precision`.
