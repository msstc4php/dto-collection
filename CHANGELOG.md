# Changelog

All notable changes to this project are documented here. Dates are UTC.

## 1.0.0 - unreleased

First release, for PHP 8.1–8.4.

### Added

- Typed lists with keys `0..n-1`, built from traits: `ImmutableListBehaviour`,
  `MutableListBehaviour` and `Doctrine\OdmListBehaviour`. Concrete classes type their elements
  natively; [docs/writing-a-typed-collection.md](docs/writing-a-typed-collection.md) is the
  contract for `dto-collection-generator`.
- Generic lists `ListOf` (immutable) and `MutableListOf` (mutable), with runtime checks
  through `ElementType`: scalars, classes, `?T`, unions and intersections, built in code or
  parsed from a declaration.
- Interfaces `ReadableList`, `ImmutableList`, `MutableList` and `TypedList`.
- Equality through `Equatable`, `StrictEquality`, `ComparatorEquality` and `EqualityResolver`.
- About 70 methods ([docs/methods.md](docs/methods.md)), and pipe functions in
  `MSSTC4PHP\DtoCollection\Pipe` for PHP 8.5's `|>`.
- A Doctrine MongoDB ODM variant usable as `collectionClass`, for doctrine/collections 2 and 3
  and ODM 2.10+ ([docs/odm.md](docs/odm.md)).
- The `#[TypedCollection]` attribute, which marks a class for `dto-collection-generator`.
- `#[\NoDiscard]` on every value-returning method, so PHP 8.5 warns about an ignored
  `with*()` result.
