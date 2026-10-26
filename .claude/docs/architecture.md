# Architecture

Library, not hexagonal: two deptrac layers. `Core` (everything but `Doctrine\*`) may not use any
vendor; `Bridge` (`MSSTC4PHP\DtoCollection\Doctrine\*`) may use Core and `Doctrine\*`.

## Storage and traits

- `Behaviour\ListStorage<T>`: the single `private array $elements` (list), the public
  `__construct(iterable $elements = [])`, `coerceAll()`, `copyWith()` (clone + assign), and the
  abstract hooks `elementType()` / `equality()`.
- Topic traits (`Reading`, `Searching`, `Aggregating`, `Slicing`, `Ordering`, `SetOperations`,
  `Structuring`, `Mapping`, `Immutable`, `Mutable`, `Numeric`, `Text`), each `@template T`.
- Aggregates: `ImmutableListBehaviour`, `MutableListBehaviour`, `Doctrine\OdmListBehaviour`
  (MutableListBehaviour + DoctrineCollectionBehaviour, with `insteadof` for the shared names).
- Element-typed methods are `protected do*()`; concrete classes add the typed public one-liners.
  `Generic{Reading,Immutable,Mutable}Facade` are those one-liners typed `mixed` with coercion,
  used by `ListOf` / `MutableListOf` (factories shared via `GenericFactories`).
- Private helpers in one trait are called from another (e.g. `kept()`, `sortedElementsBy()`,
  `reduced()`); fine because traits compose into one class.

## Interfaces

`ReadableList<T>` ← `ImmutableList<T>`, `MutableList<T>`, `TypedList<T>` (static factories).
Doctrine-shared names have no native return type in the interfaces (see integrations.md).

## Pipe

`src/Pipe/functions.php` (Composer `files`), generated once by a script and then edited by
hand; `FunctionsTest` pins that every ReadableList/ImmutableList method has a function with
the same parameter names.
