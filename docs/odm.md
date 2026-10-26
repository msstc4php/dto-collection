# Doctrine MongoDB ODM

The ODM variant is a mutable list that also implements
`Doctrine\Common\Collections\Collection`, so a document field can hold it directly:

```php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class Owner
{
    #[ODM\EmbedMany(targetDocument: Pet::class, collectionClass: PetCollection::class, strategy: 'setArray')]
    private PetCollection $pets;

    public function __construct(Pet ...$pets)
    {
        $this->pets = PetCollection::of(...$pets);
    }

    public function pets(): PetCollection
    {
        return $this->pets;
    }
}
```

After `find()`, `$owner->pets()` is still a `PetCollection`. ODM subclasses it, and the
subclass records your changes. Both kinds of methods mark the document dirty: the typed ones
(`push`, `sortBy`, `swap`, …) and Doctrine's (`add`, `removeElement`, …).

## Storage strategy

Use `strategy: 'setArray'` or `'atomicSetArray'` for `EmbedMany` and `ReferenceMany`. The list
reindexes on every change, and these strategies store a BSON array. ODM's default `pushAll`
keeps keys and can store an object after a removal.

## Rules for an ODM collection class

ODM generates a persistent subclass of your class and redeclares every `Collection` method in
it. PHP requires your declarations to stay compatible with that subclass.

- **Not `final`.** The class needs a constructor with no required argument
  (`ListStorage`'s `__construct(iterable $elements = [])`) and no object or enum defaults.
- **The names Doctrine declares keep Doctrine's signatures.** These are `add`, `set`, `get`,
  `remove`, `removeElement`, `contains`, `first`, `last`, `filter`, `map`, `toArray`,
  `count`, `getIterator`, the `offset*` methods and the rest of `Collection`. They take
  `mixed`, check the element type at runtime and throw `ElementTypeMismatch`. Do not narrow
  their return types: `first(): ?Pet` is a fatal error once ODM generates the subclass.
  `OdmListBehaviour` declares them for you, with the signatures of the installed
  doctrine/collections major (2: no native return types; 3: Collection's native types), as
  ODM itself does.
- **Narrow freely what Doctrine does not declare:** `firstOrFail(): Pet`, `at(): ?Pet`,
  `find(): ?Pet`, `push(Pet ...$elements)`, `pop(): ?Pet`, `positionOf(Pet $element)`, …
- **Methods that return the list declare `self`, never `static`.** The generated subclass
  copies `static` and then returns the inner collection, which is a `TypeError`.
- **No parameter of the form `?T $x = null` in your own public methods.** The generator drops
  the `?`, and PHP 8.4 deprecates the implicitly nullable result. This is why
  `sliced(int $offset, int $length = PHP_INT_MAX)` has no null default.
- **No `doAdd`, `doRemove`, `doSet` methods.** ODM's trait declares them private. The
  library's own protected methods are `doRemoveAt` and `doSetAt` for this reason.

- **Implement `Selectable` explicitly.** On doctrine/collections 2, `Collection` does not
  extend it, and ODM's `matching()` throws a `LogicException` without it.
- **Mutate through the field, not through a returned `$this`.** On a loaded collection,
  `each()` and `pipe()` without steps return ODM's inner collection, not the persistent
  wrapper. Chaining a mutator onto their result (`$owner->pets()->each(...)->push($pet)`)
  bypasses the wrapper. With `NOTIFY` change tracking, such a change is lost.

`tests/Fixture/Odm/PetCollection.php` is the reference class. Without a MongoDB server,
`tests/Unit/Doctrine/PetCollectionTest::testOdmCanGenerateItsPersistentSubclass` checks that
ODM can generate the subclass.

## No null elements

ODM 2.17 fails on a `null` element of `EmbedMany` with a `TypeError` in
`UnitOfWork::getParentAssociation()`, already at `persist()`. An ODM collection therefore
cannot be nullable, and `#[TypedCollection(variant: Variant::Odm, nullable: true)]` throws
`InvalidArgument`.

## Differences from ArrayCollection

| Method | ArrayCollection | ODM variant |
|---|---|---|
| `first()`, `last()` on an empty list | `false` | `null`, the same as every other variant |
| `remove(0)` | leaves a hole: keys `[1, 2]` | reindexes: keys `[0, 1]` |
| `get('a')`, `remove('a')` | look up the string key | `null`: the keys are integers |
| `set('a', $x)`, `$list['a'] = $x` | store under `'a'` | `InvalidArgument` |
| `slice()`, `filter()`, `matching()` | keep the original keys | reindex |
| `indexOf($missing)` | `false` | `false` (`positionOf()` returns `null`) |
| `current()`, `next()` past the end | `false` | `false` |
| `add()`, `set()`, `contains()` with a wrong type | accepted | `ElementTypeMismatch` |

`exists`, `forAll`, `findFirst` and `partition` call your closure with `(key, element)`,
as Doctrine does. Every other method calls it with `(element, index)`, and passes the index
only when the closure declares a second parameter.

`matching(Criteria)` applies the criteria the way `ArrayCollection` does. The result is an
instance of your class, reindexed.
