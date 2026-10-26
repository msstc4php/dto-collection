# Integrations

## Doctrine MongoDB ODM (`collectionClass`)

ODM instantiates `new $collectionClass()` for the inner collection and generates a persistent
subclass `extends YourClass` that `use`s `PersistentCollectionTrait` (+ `PersistentCollectionCompatibility`).
Consequences, all verified with `DefaultPersistentCollectionGenerator` (AUTOGENERATE_EVAL):

1. The trait **redeclares every Collection method** with the installed doctrine/collections
   major's signatures (v2: untyped, `add(): bool`; v3: Collection's native types,
   `filter()/map(): Collection`, `getIterator(): Traversable`). Our same-named methods must be
   compatible → no narrowed returns on those names; `ReadableList`/`MutableList` declare them
   without native return types.
2. ODM 2.10–2.16 declare `clear()` untyped; 2.17 `clear(): void` → our `clear()` untyped on v2.
3. The trait has **private `doAdd`/`doRemove`/`doSet`** → our protected methods with those names
   are a fatal "access level" error → ours are `doRemoveAt`/`doSetAt`.
4. Generated wrappers for our other public methods copy the signature; `static` returns break
   (returns the inner collection), `self` works. `?T $x = null` is rendered `T $x = NULL`
   (implicit nullable, deprecated on 8.4) — `sliced()` uses `int $length = PHP_INT_MAX`.
5. Dirty detection: `isDirty()` compares `coll->toArray()` with the snapshot, so typed mutators
   persist without calling `changed()`.
6. On doctrine/collections 2, `Collection` does not extend `Selectable`, but ODM's `matching()`
   checks `$this->coll instanceof Selectable` → ODM collection classes `implements Selectable`.
7. `PersistentCollectionTrait::initialize()`/`takeSnapshot()` call `add()` once per element →
   `doPush` must append in place (was O(n) per call, quadratic hydration).
8. `each()`/`pipe()` return `$this` → on the persistent wrapper that is the inner collection.
9. ODM rejects `null` EmbedMany elements (`UnitOfWork::getParentAssociation()` TypeError at
   persist) → `TypedCollection` forbids nullable ODM collections.

`DoctrineCollectionBehaviour.php` declares the trait twice inside
`if (class_exists(Criteria::class) && !defined(Criteria::class.'::ASC'))` (ODM's own switch;
`defined()` alone does not autoload). The file is excluded from Rector and PHP-CS-Fixer, and
PHPStan inline ignores do not work there (see known-issues.md).

`PetCollectionTest::testOdmCanGenerateItsPersistentSubclass` runs the generator in a
subprocess with deprecations on — the cheap guard for all of the above.
