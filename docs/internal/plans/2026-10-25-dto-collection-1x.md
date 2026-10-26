# dto-collection 1.x Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build `msstc4php/dto-collection` 1.x: typed list collections for PHP 8.1–8.4. The line has immutable, mutable and Doctrine MongoDB ODM variants, generic `ListOf`/`MutableListOf`, and pipe functions.

**Architecture:** The behaviour lives in traits over one private `list<mixed>` property. Every trait method whose signature names the element type `T` is `protected` with a `do` prefix. A concrete class (written by hand now, by `dto-collection-generator` later) exposes natively typed public one-liners that delegate to those methods. `ListOf`/`MutableListOf` expose the same names, typed `mixed`, and check types at runtime through `ElementType`. Equality is a pluggable `Equality` strategy.

**Tech Stack:** PHP 8.1 syntax, PHPUnit 10.5, PHPStan 2 (level max + strict rules + disallowed calls), PHP-CS-Fixer, Rector 2, Deptrac, Infection 0.32, doctrine/collections `^2.1 || ^3.1`, doctrine/mongodb-odm `^2.10` (dev and suggest only), MongoDB 7 in Docker.

**Spec:** `docs/internal/specs/2026-10-25-dto-collection-design.md`

## Global Constraints

- PHP `>=8.1 <8.5` for line 1.x. No PHP 8.2+ syntax (readonly classes, DNF types, typed class constants, property hooks) and no 8.2+ functions or classes. Enforced by PHPStan `phpVersion: 80100`, the disallowed-calls lists and `make lint-81`.
- Package `msstc4php/dto-collection`, namespace `MSSTC4PHP\DtoCollection`, directory `~/PhpstormProjects/msstc4php/dto-collection`, git default branch `1.x`.
- Keys are always `0..n-1` consecutive. Every operation reindexes.
- The core never references `Doctrine\*`. Only `MSSTC4PHP\DtoCollection\Doctrine\*` may, enforced by Deptrac. `doctrine/collections` and `doctrine/mongodb-odm` appear only in `require-dev` and `suggest`.
- Instance methods that return the collection declare native **`self`, never `static`**. ODM's generated persistent subclass copies `static` and then returns the inner collection, which is a TypeError (verified in `DefaultPersistentCollectionGenerator::formatType`, ODM 2.17). In PHPDoc use `@return self<T>` / `@return list<self<T>>`.
- A trait method that names `T` in a parameter or the return is `protected function do<PublicName>(...)`. A trait method that does not is public in the trait.
- `#[\NoDiscard]` goes on every public method that returns a value. Exceptions: `each()`, and the mutators `remove`, `removeElement`, `removeIf`, `pop`, `shift`. Never on `void` methods.
- Closures are native `Closure`. Predicates and mappers receive `(T $element, int $index)`. Selectors (`sortedBy`, `sortBy`, `uniqueBy`, `diffBy`, `intersectBy`, `sumBy`, `averageBy`, `minBy`, `maxBy`, `keyBy`, `countBy`) receive `(T $element)`. `reduce` receives `(R $carry, T $element, int $index)`, comparators `(T, T)`, `chunkWhile` `(T $previous, T $current)`. The index is passed only to callbacks that declare ≥ 2 parameters or are variadic (Task 4, `Callback`).
- Public names are spelled out in full (`integers`, `average`). `min`/`max` are allowed.
- Code comments in English, only the non-obvious why. No `@phpstan-ignore` without a `Reason:` line. No baseline.
- Mutation testing with `--threads=4` at most. The target is MSI 100 %, and `minMsi: 99` with equivalent mutants listed in `infection.json5` with a reason, like dto-generator.
- Every task: tests first, then `make fix`, `make check` and `make test` all clean, then commit. Commit messages end with `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.
- Dates in docs and changelog are UTC.

## Review Focus

1. **Internal functions as callbacks:** `filter(is_string(...))`, `transform(strtoupper(...))`. They must not throw `ArgumentCountError` because of the extra index argument. Pinned in Task 4 (`CallbackTest`) and Task 7.
2. **Float collections receiving an int:** `ListOf::floats([1, 2])` and `MutableListOf::push(3)` on a float list store `1.0, 2.0, 3.0`, the same as a native `float` parameter under `strict_types`. `int|float` keeps the int. Pinned in Task 2 (`ElementTypeTest`).
3. **`NAN` and `-0.0` in equality:** `NAN` never equals itself (`===` semantics), so `unique()` keeps every `NAN` and `contains(NAN)` is false. `-0.0` equals `0.0`. Pinned in Task 3 and Task 8.
4. **ODM persistent proxy:** on a collection loaded from MongoDB, queries (`filter`, `sorted`) return without a TypeError, and typed mutators (`push`, `sort`) mark the document dirty and persist. Pinned in Task 14.
5. **Mutating while iterating:** `foreach ($mutable as $e) { $mutable->push($e); }` iterates the snapshot taken at `getIterator()` and terminates. Pinned in Task 11.

---

## File map

```
composer.json  tools/composer.json  Makefile  phpstan.dist.neon  .php-cs-fixer.dist.php  rector.php
deptrac.yaml   infection.json5  phpunit.xml.dist  docker-compose.yaml (mongo)  .github/workflows/ci.yml
CLAUDE.md  README.md  CHANGELOG.md  LICENSE  docs/*.md  .claude/docs/*.md
src/
  ElementType.php                  type model: accepts/coerce/describe/parse
  Internal/ElementTypeParser.php   string grammar → ElementType (@internal)
  Internal/Callback.php            index-aware callback invocation (@internal)
  Internal/HashSet.php             hash/pairwise membership over an Equality (@internal)
  Equatable.php
  Equality/{Equality,StrictEquality,ComparatorEquality,EqualityResolver}.php
  Exception/{CollectionException,ElementTypeMismatch,InvalidElementType,IndexOutOfRange,
             EmptyCollection,NotExactlyOneElement,ReadOnlyCollection,UnsupportedOperation,InvalidArgument}.php
  ReadableList.php  ImmutableList.php  MutableList.php  TypedList.php
  Behaviour/ListStorage.php        property + abstract elementType()/equality() + helpers
  Behaviour/{Reading,Searching,Aggregating,Slicing,Ordering,SetOperations,Structuring,Mapping}Behaviour.php
  Behaviour/{Numeric,Text}Behaviour.php
  Behaviour/{Immutable,Mutable}Behaviour.php
  Behaviour/{ImmutableList,MutableList}Behaviour.php   aggregates ("everything") per variant
  Behaviour/Generic{Reading,Immutable,Mutable}Facade.php  public mixed facades for ListOf/MutableListOf
  ListOf.php  MutableListOf.php
  Attribute/{TypedCollection,Variant}.php
  Doctrine/DoctrineCollectionBehaviour.php  Doctrine/OdmListBehaviour.php
  Pipe/functions.php
tests/
  Fixture/{Item,Tag,Cat,Dog}.php
  Fixture/{ItemList,MutableItemList,IntegerList}.php     generator-style typed classes
  Fixture/Odm/{Pet,PetCollection,Owner,OwnerCollection,Holder}.php
  Contract/*Contract.php           abstract suites run against every variant
  Unit/**  Integration/Odm/**
```

---

### Task 1: Repository and tooling scaffold

**Files:**
- Create: everything in the file map's root row, plus `tests/bootstrap.php`, `tests/Unit/SmokeTest.php`, `CLAUDE.md`, `.gitignore`, `.gitattributes`, `.editorconfig`, `LICENSE` (MIT).
- Move in: `docs/internal/specs/…design.md` and this plan, which already exist.

**Interfaces:**
- Produces: `make install|fix|check|test|test-81|test-odm|lint-81|verify|infection|bc-check|help`.

- [ ] **Step 1:** Run `git init -b 1.x` in `dto-collection`. Commit the existing `docs/internal/` alone as the first commit: `docs: dto-collection design and 1.x plan`.
- [ ] **Step 2:** Write `composer.json`:
  - `"php": ">=8.1 <8.5"`;
  - `require-dev`: `phpunit/phpunit: ^10.5`, `doctrine/collections: ^2.1 || ^3.1`, `doctrine/mongodb-odm: ^2.10`;
  - `suggest`: the two doctrine packages;
  - `autoload`: psr-4 `MSSTC4PHP\\DtoCollection\\` → `src/`, and `files`: `src/Pipe/functions.php`, created empty with a namespace;
  - `autoload-dev`: `MSSTC4PHP\\DtoCollection\\Tests\\` → `tests/`.

  Write `tools/composer.json`: copy dto-generator's `tools/composer.json` and change name and description.
- [ ] **Step 3:** Write the configs by copying dto-generator's and changing:
  - **`phpstan.dist.neon`:** `phpVersion: 80100`; drop the 8.0/8.1 functions from `disallowedFunctionCalls` (keep only 8.2+, starting at `ini_parse_quantity`, `memory_reset_peak_usage`, `json_validate`, …); `disallowedClasses` keeps only 8.2+ (`Random\Randomizer`, `SensitiveParameter`, `AllowDynamicProperties`, `Override`, `Deprecated`, `DateMalformedStringException`), with the message `PHP 8.2+ only; 1.x runs on PHP 8.1`.
  - **`rector.php`:** `PhpVersion::PHP_81`, `withPhpSets(php81: true)`.
  - **`.php-cs-fixer.dist.php`:** `@PHP81Migration(:risky)`; drop the 7.4 trailing-comma rule.
  - **`phpunit.xml.dist`:** suites `unit` (`tests/Unit`) and `integration` (`tests/Integration`); `tests/Contract` holds only abstract classes; `failOnRisky`, `failOnWarning`, `failOnDeprecation`, `displayDetailsOnTestsThatTriggerWarnings`.
  - **`deptrac.yaml`:** layers `Core` (`^MSSTC4PHP\\DtoCollection\\(?!Doctrine\\)`), `Bridge` (`^MSSTC4PHP\\DtoCollection\\Doctrine\\`), `DoctrineVendor` (`^Doctrine\\`). Rule: Core → nothing; Bridge → Core, DoctrineVendor.
  - **`infection.json5`:** source `src`, `minMsi: 99`, `minCoveredMsi: 99`, timeout 30.
- [ ] **Step 4:** Write the `Makefile`:
  - copy dto-generator's targets, with `PHP81 := docker run --rm --user "$$(id -u):$$(id -g)" -v "$(CURDIR):/app" -w /app php:8.1-cli`;
  - `lint-81` lints `src tests`;
  - `test-81` runs `--testsuite=unit` on 8.1;
  - `test-odm` runs `docker compose up -d --wait mongo` and then `DTO_COLLECTION_MONGODB_URI=mongodb://127.0.0.1:27018 vendor/bin/phpunit --testsuite=integration`;
  - `verify: check test test-81`.

  `docker-compose.yaml`: service `mongo` with image `mongo:7`, port `27018:27017`, and a healthcheck running `mongosh --eval 'db.runCommand({ping:1})'`.
- [ ] **Step 5:** Write `tests/Unit/SmokeTest.php` with `testAutoloads(): self::assertTrue(interface_exists(\Countable::class))`. Run `make install && make verify`. Expected: all green.
- [ ] **Step 6: `#[\NoDiscard]` probe.** Create the throwaway `src/Probe.php` with one `#[\NoDiscard] public function value(): int`. Run `make check`.
  - If PHPStan reports an unknown attribute class, add `stubs/NoDiscard.php` (`#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)] final class NoDiscard { public function __construct(public readonly ?string $message = null) {} }`, no namespace) to `stubFiles`, then rerun.
  - Delete `Probe.php`.
- [ ] **Step 7:** Write `CLAUDE.md`, modelled on dto-generator's: what this is, the commands, the conventions above, and a pointer to the spec and plan. Commit: `chore: scaffold dto-collection 1.x tooling`.

---

### Task 2: Exceptions, `ElementType`, parser, attribute

**Files:**
- Create: `src/Exception/*.php`, `src/ElementType.php`, `src/Internal/ElementTypeParser.php`, `src/Attribute/{TypedCollection,Variant}.php`
- Test: `tests/Unit/ElementTypeTest.php`, `tests/Unit/ElementTypeParserTest.php`, `tests/Unit/Attribute/TypedCollectionTest.php`, `tests/Fixture/{Item,Tag,Cat,Dog}.php`

**Interfaces:**
- Produces:
  - `interface Exception\CollectionException extends \Throwable`. All exceptions implement it:
    - `ElementTypeMismatch extends \InvalidArgumentException`, with `static at(int $index, ElementType $expected, mixed $actual): self` and the message `Element #%d must be %s, %s given` (actual = `get_debug_type`);
    - `InvalidElementType extends \InvalidArgumentException`;
    - `IndexOutOfRange extends \OutOfRangeException`, with `static for(int $index, int $count): self`;
    - `EmptyCollection extends \UnderflowException`;
    - `NotExactlyOneElement extends \LengthException`;
    - `ReadOnlyCollection extends \LogicException`;
    - `UnsupportedOperation extends \LogicException`;
    - `InvalidArgument extends \InvalidArgumentException`.
  - `final class ElementType`, `@template T`, private constructor:
    - `static integer(): self<int>`, `string(): self<string>`, `float(): self<float>`, `boolean(): self<bool>`;
    - `static ofClass(class-string<C> $class): self<C>`;
    - `static intersection(class-string ...$classes): self<object>`;
    - `static union(self<U> ...$types): self<U>`;
    - `static parse(string $declaration): self<mixed>`;
    - `nullable(): self<T|null>`, `isNullable(): bool`, `accepts(mixed $value): bool`;
    - `coerce(mixed $value, int $index): T`, which throws `ElementTypeMismatch`;
    - `isNumeric(): bool` (every branch is int or float), `isString(): bool`;
    - `objectBranches(): list<list<class-string>>` (each branch is a class list; one element = single class);
    - `describe(): non-empty-string` (canonical: branches in declaration order, `null` last, classes without a leading `\`);
    - `equals(self $other): bool`.
  - `ElementTypeParser::parse(string): ElementType` (`@internal`).
  - `#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)] final class Attribute\TypedCollection`, with `__construct(public readonly string $class, public readonly Variant $variant, public readonly bool $nullable = false, public readonly ?string $elementType = null)`. A non-null `elementType` is validated with `ElementType::parse`; an empty `class` → `InvalidArgument`.
  - `enum Attribute\Variant: string { case Immutable = 'immutable'; case Mutable = 'mutable'; case Odm = 'odm'; }`.
  - Fixtures:
    - `Item`: final, implements `Equatable` (Task 3; until then the plain method), `__construct(public readonly string $id, public readonly int $score)`, `equals(object $other): bool` → `$other instanceof self && $other->id === $this->id`;
    - `Tag`: final, `__construct(public readonly string $name)`, no equals;
    - `Cat`: implements `Equatable`, equal by `name`;
    - `Dog`: no equals.

- [ ] **Step 1: Write the failing tests:**

```php
// ElementTypeTest
testScalarsAcceptOnlyTheirType: integer() accepts 1, rejects 1.0,'1',true,null
testFloatCoercesIntLikeStrictTypes: float()->coerce(1, 0) === 1.0; union(integer(), float())->coerce(1, 0) === 1
testBooleanRejectsInts: boolean()->accepts(1) === false
testClassAcceptsSubtypes: ofClass(\Countable::class)->accepts(new \ArrayObject()) === true
testIntersectionNeedsAll: intersection(\Countable::class, \Traversable::class) accepts ArrayObject, rejects a Countable-only stub
testNullable: ofClass(Item::class)->nullable() accepts null; isNullable(); describe() === 'MSSTC4PHP\DtoCollection\Tests\Fixture\Item|null'
testUnionDescribe: union(ofClass(Cat::class), ofClass(Dog::class))->nullable()->describe() === '…\Cat|…\Dog|null'
testCoerceMismatchMessage: expectExceptionMessage('Element #3 must be int, string given') on integer()->coerce('x', 3)
testOfClassRejectsUnknownClass: expect InvalidElementType for ofClass('No\Such')
testIsNumericAndIsString: union(integer(), float())->isNumeric(); string()->isString(); ofClass(Item)->isNumeric() === false
testEquals: parse('int|null')->equals(integer()->nullable())

// ElementTypeParserTest (data provider: declaration → describe())
'int', ' int | null ', '?int' → 'int|null'; '\Countable&\Traversable' → 'Countable&Traversable'; 'Cat|Dog|null'
rejects with InvalidElementType: '', 'null', 'mixed', 'array', 'callable', 'iterable', 'object', 'self', 'static', 'void',
'never', '?int|string', '?A&B', '(A&B)|null', 'A&B|C', 'int|int', 'No\Such', 'int|'

// TypedCollectionTest
testRepeatableOnClass: reflection on a fixture class with two attributes → two instances, variants Immutable and Odm
testRejectsInvalidElementType: new TypedCollection('X', Variant::Mutable, elementType: 'mixed') → InvalidElementType
```

- [ ] **Step 2:** Run `vendor/bin/phpunit tests/Unit`. Expected: FAIL (classes missing).
- [ ] **Step 3:** Implement. The parser tokenizes on `|` and `&` after trimming:
  - `?T` is allowed only as the whole declaration;
  - mixing `|` and `&` → DNF → reject;
  - scalars are case-insensitive;
  - classes go through `class_exists || interface_exists || enum_exists`.

  `coerce` returns `(float) $value` only when the value is an int, the float branch exists and no int branch exists.
- [ ] **Step 4:** Run the tests. Expected: PASS. Then `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: element type model, parser, exceptions, TypedCollection attribute`.

---

### Task 3: Equality

**Files:**
- Create: `src/Equatable.php`, `src/Equality/{Equality,StrictEquality,ComparatorEquality,EqualityResolver}.php`, `src/Internal/HashSet.php`
- Test: `tests/Unit/Equality/*Test.php`, `tests/Unit/Internal/HashSetTest.php`

**Interfaces:**
- Consumes: `ElementType::objectBranches()`.
- Produces:
  - `interface Equatable { public function equals(object $other): bool; }`;
  - `interface Equality { equals(mixed $left, mixed $right): bool; hashKey(mixed $value): ?string; }`. A `null` hash means "compare pairwise";
  - `final class StrictEquality implements Equality`. `===`, plus `-0.0 == 0.0`. Keys: `'n'`, `'b:0|1'`, `'i:'.$v`, `'s:'.$v`, `'f:'.($v == 0.0 ? '0' : var_export($v, true))`, `'o:'.spl_object_id`. `NAN` → `null` hash;
  - `final class ComparatorEquality implements Equality`, with `__construct(array<class-string, \Closure(object, object): bool> $comparators)`. The first class `C` with both values `instanceof C` decides; otherwise strict. `hashKey` is `null` for instances of any `C`, otherwise strict;
  - `final class EqualityResolver`, with `static forType(ElementType $type): Equality`. A single-class branch whose class implements `Equatable` → comparator `static fn (Equatable $a, object $b): bool => $a->equals($b)`. No such branch → `StrictEquality`;
  - `@internal final class HashSet`, with `__construct(Equality $equality, iterable<mixed> $values)` and `contains(mixed $value): bool`. It uses hashed buckets when every value hashes, otherwise a pairwise scan; a `null`-hash probe scans pairwise.

- [ ] **Step 1: Write the failing tests:**

```php
testStrictScalars: equals(1, 1); !equals(1, 1.0); !equals('1', 1); equals(null, null); equals(-0.0, 0.0); !equals(NAN, NAN)
testStrictObjectsByIdentity: !equals(new Tag('a'), new Tag('a')); same instance equal; hashKey(-0.0) === hashKey(0.0); hashKey(NAN) === null
testComparatorUsesClosureForItsClass: equals(new Item('a',1), new Item('a',9)); hashKey(new Item('a',1)) === null
testComparatorDifferentBranchesNeverEqual: [Cat => cmp] : !equals(new Cat('x'), new Dog('x'))
testComparatorFallsBackToStrictForOtherValues: equals(5, 5); !equals(new Dog('x'), new Dog('x'))
testResolverPicksEquatable: forType(ofClass(Item))->equals(Item a/1, Item a/2) === true
testResolverStrictWithoutEquatable: forType(ofClass(Tag)) instanceof StrictEquality
testResolverUnion: forType(union(Cat, Dog)) — Cat by name, Dog by identity
testHashSetHashedAndPairwise: contains works for ints, for Items (pairwise), NAN never contained, mixed set of Items and ints
```

- [ ] **Step 2:** Run the tests. Expected: FAIL.
- [ ] **Step 3:** Implement as specified. Make `Item`/`Cat` implement `Equatable`.
- [ ] **Step 4:** Run the tests: PASS. Then `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: equality strategies`.

---

### Task 4: Core skeleton: `ReadableList`, `ListStorage`, `ReadingBehaviour`, `Callback`, `ListOf` factories

**Files:**
- Create: `src/ReadableList.php`, `src/Behaviour/ListStorage.php`, `src/Behaviour/ReadingBehaviour.php`, `src/Behaviour/GenericReadingFacade.php`, `src/Internal/Callback.php`, `src/ListOf.php`
- Test: `tests/Unit/Internal/CallbackTest.php`, `tests/Contract/ReadableListContract.php`, `tests/Unit/ListOf/ListOfReadingTest.php`, `tests/Unit/ListOf/ListOfFactoryTest.php`

**Interfaces:**
- Produces:
  - `interface ReadableList`, `@template T`, extends `\IteratorAggregate<int, T>, \Countable, \JsonSerializable, \ArrayAccess<int, T>`. Methods added here: `isEmpty(): bool`, `isNotEmpty(): bool`, `toArray(): array` (`list<T>`), `jsonSerialize(): array`, `getIterator(): \ArrayIterator`, `equals(ReadableList $other): bool`. Tasks 5–9 add their public methods to this interface.
  - `trait ListStorage`:
    - `private array $elements = []` (`list<mixed>`);
    - `abstract protected function elementType(): ElementType;` and `abstract protected function equality(): Equality;`;
    - `public function __construct(iterable $elements = [])` → `$this->elements = $this->coerceAll($elements)`;
    - `protected function coerceAll(iterable $values): array` (keys discarded, `coerce` with running index);
    - `protected function copyWith(array $elements): self` (`clone $this` and assign; callers pass already valid lists).
  - `trait ReadingBehaviour`: `count`, `isEmpty`, `isNotEmpty`, `toArray`, `jsonSerialize`, `getIterator`, `offsetExists(mixed): bool` (int in range), `offsetGet(mixed): mixed` (= `doGet`, `null` when missing), `offsetSet`/`offsetUnset` → `ReadOnlyCollection`, `equals(ReadableList)`, plus `protected doGet(int $index): mixed`.
  - `@internal final class Callback`, with `static invoke(\Closure $callback, mixed $element, int $index): mixed`, which passes `$index` only when the callback has ≥ 2 parameters or is variadic. Arity is cached per closure via `\WeakMap`.
  - `final class ListOf implements ReadableList` (`@template T`; implements `ImmutableList` from Task 10 on). Its own private `__construct(ElementType $type, Equality $equality, array $elements)` overrides the trait constructor. Static factories:
    - `of(class-string<C> $class, iterable $elements = [], ?Equality $equality = null): self<C>`;
    - `integers(iterable $elements = []): self<int>`, plus `strings`, `floats`, `booleans`;
    - `ofType(ElementType<U>|string $type, iterable $elements = [], ?Equality $equality = null): self<U>`.

    `$equality ?? EqualityResolver::forType($type)`.
  - `trait GenericReadingFacade`: public `get(int $index): mixed` → `doGet`. Grows in Task 5.
  - `abstract class Contract\ReadableListContract extends TestCase`, with `abstract protected function items(Item ...$items): ReadableList` and `abstract protected function tags(Tag ...$tags): ReadableList`.

- [ ] **Step 1: Write the failing tests:**

```php
// CallbackTest
testPassesIndexToTwoParameterClosure: invoke(fn ($e, $i) => $i, 'a', 7) === 7
testOmitsIndexForInternalSingleArgFunction: invoke(strtoupper(...), 'a', 7) === 'A'   // Review Focus 1
testPassesIndexToVariadic: invoke(fn (...$a) => count($a), 'a', 1) === 2

// ReadableListContract (each runs on every variant)
testCountAndEmptiness: items() is empty; items(a, b) count 2, isNotEmpty
testToArrayIsListAndDetached: $a = items(a)->toArray(); $a[] = b; original count still 1
testIteratorIsIndexed: iterator_to_array(items(a, b)) === [0 => a, 1 => b]
testJsonSerialize: json_encode(items(new Item('x', 1))) === '[{"id":"x","score":1}]'
testOffsetReadOnly: isset($l[1]) false for one-element list; $l[0] === a; $l[5] === null; $l[0] = a → ReadOnlyCollection
testListEquals: items(Item a/1)->equals(items(Item a/9)) true (Equatable); tags(new Tag x)->equals(tags(new Tag x)) false

// ListOfFactoryTest
testFactoriesValidate: ListOf::integers([1, 'x']) → ElementTypeMismatch 'Element #1 must be int, string given'
testKeysDiscarded: ListOf::integers([5 => 1, 'a' => 2])->toArray() === [1, 2]
testFloatsCoerceInts: ListOf::floats([1, 2])->toArray() === [1.0, 2.0]   // Review Focus 2
testOfTypeString: ListOf::ofType('?int', [null, 1])->toArray() === [null, 1]
testExplicitEqualityWins: ListOf::of(Tag::class, [t], new ComparatorEquality([Tag::class => fn ($a, $b) => $a->name === $b->name]))
                          ->equals(ListOf::of(Tag::class, [new Tag(t->name)])) === true
```

`ListOfReadingTest extends ReadableListContract` implements `items()` as `ListOf::of(Item::class, $items)`, and the same for tags.

- [ ] **Step 2:** Run the tests: FAIL.
- [ ] **Step 3:** Implement. Make sure `ListOf` declares `elementType()`/`equality()` returning its private properties.
- [ ] **Step 4:** Run the tests: PASS. Then `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: list storage, reading behaviour and ListOf factories`.

---

### Task 5: Access, search, predicates

**Files:**
- Create: `src/Behaviour/SearchingBehaviour.php`
- Modify: `ReadableList`, `GenericReadingFacade`, `ListOf` (use the trait)
- Test: `tests/Contract/SearchingContract.php` (abstract; `ListOfSearchingTest` extends it)

**Interfaces:**
- Produces:
  - Protected (T in the signature): `doFirst(): mixed`, `doLast()`, `doFirstOrFail()`/`doLastOrFail()` (→ `EmptyCollection`), `doGetOrFail(int)` (→ `IndexOutOfRange`), `doAt(int $position)` (negative counts from the end, out of range → null), `doSingle()` (≠ 1 element → `NotExactlyOneElement`), `doSingleOrNull()` (null unless exactly one), `doRandom()`, `doContains(mixed)`, `doContainsAll(mixed ...)`, `doContainsAny(mixed ...)`, `doPositionOf(mixed): ?int`, `doFind(\Closure): mixed`, `doFindLast(\Closure): mixed`.
  - Public in the trait and in `ReadableList`: `findPosition(\Closure): ?int`, `any(\Closure): bool`, `all(\Closure): bool` (true on empty), `none(\Closure): bool`.
  - Element-returning methods are also added to `ReadableList` with return type `mixed` (`first`, `last`, `firstOrFail`, `lastOrFail`, `get`, `getOrFail`, `at`, `single`, `singleOrNull`, `random`, `find`, `findLast`). Concrete classes narrow the return natively.
  - `GenericReadingFacade` gains public `first … findLast` returning `mixed`. It also gains `contains(mixed)`, `containsAll(mixed ...)`, `containsAny(mixed ...)` and `positionOf(mixed)`, each coercing its arguments via `elementType()->coerce($v, 0)` first.
  - Contract abstract additions: `nullableItems(?Item ...$items): ReadableList`.

- [ ] **Step 1: Write the failing tests (contract):**

```php
testFirstLastOnEmptyAreNull; testFirstOrFailOnEmptyThrows EmptyCollection
testFirstOnNullableIsAmbiguousButOrFailIsNot: nullableItems(null, a)->first() === null; ->firstOrFail() === null (no throw)
testFirstDoesNotTreatFalsyAsMissing: ListOf::integers([0])->first() === 0   // lodash head() bug
testGetAndAt: get(1) === b; get(-1) === null; at(-1) === last; at(9) === null; getOrFail(9) → IndexOutOfRange 'Index 9 is out of range for 2 elements'
testSingle: items(a)->single() === a; items()->single() and items(a,b)->single() → NotExactlyOneElement; singleOrNull null for both
testContainsUsesEquality: items(Item a/1)->contains(new Item('a', 5)) true; tags(t)->contains(new Tag(t->name)) false
testContainsAllAny: containsAll() with no args true; containsAny() with no args false
testPositionOf: first matching position; null when absent
testFindAndFindLastAndFindPosition with predicates using the index: find(fn ($e, $i) => $i === 1) === b
testAnyAllNone: all() on empty true; any() on empty false; none() on empty true
testRandomReturnsMember: items(a,b)->random() in [a,b]; items()->random() null
testContainsRejectsWrongTypeOnGeneric (ListOf only): ListOf::integers([1])->contains('1') → ElementTypeMismatch
```

- [ ] **Step 2:** Run: FAIL. **Step 3:** Implement. **Step 4:** Run: PASS; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: access, search and predicates`.

---

### Task 6: Aggregates, numeric and text behaviour

**Files:**
- Create: `src/Behaviour/{Aggregating,Numeric,Text}Behaviour.php`
- Modify: `ReadableList`, `GenericReadingFacade`, `ListOf`
- Test: `tests/Contract/AggregatingContract.php`, `tests/Unit/ListOf/ListOfNumericTest.php`

**Interfaces:**
- Produces:
  - Public, in `ReadableList`: `reduce(\Closure $reducer, mixed $initial = null): mixed` (exactly Doctrine's signature), `countWhere(\Closure): int`, `countBy(\Closure): array` (`array<array-key, positive-int>`, first-seen key order), `sumBy(\Closure): int|float` (0 on empty), `averageBy(\Closure): ?float` (null on empty).
  - Protected: `doMinBy(\Closure): mixed`, `doMaxBy(\Closure): mixed`. They compare selector values with `<=>`, the first of equals wins, null on empty. `GenericReadingFacade` gains `minBy`/`maxBy`. `ReadableList` gains them with return type `mixed`.
  - `trait NumericBehaviour`: `sum(): int|float`, `average(): ?float`, `min(): int|float|null`, `max(): int|float|null`. Each starts with `if (!$this->elementType()->isNumeric()) throw UnsupportedOperation`.
  - `trait TextBehaviour`: `join(string $separator = ''): string`, which requires `isString()`.
  - Both traits are used by `ListOf`, `MutableListOf` and numeric/string generated classes. They are **not** in `ReadableList`.

- [ ] **Step 1: Failing tests:**

```php
testReduceWithIndex: items(a,b)->reduce(fn ($c, $e, $i) => $c + $i, 0) === 1
testReduceDefaultInitialNull: items()->reduce(fn ($c, $e) => $c) === null
testCountByKeepsFirstSeenOrder; testCountWhere
testSumAndAverageBy: sumBy(score) of [1,2] === 3; averageBy === 1.5; items()->averageBy(...) === null; items()->sumBy(...) === 0
testMinMaxByFirstOfEqualsWins: two items with score 1 → minBy returns the first
// ListOfNumericTest
testSumAverageMinMax: integers([3,1,2]) sum 6, average 2.0, min 1, max 3; integers([]) min null, average null
testNumericOnNonNumericThrows: ListOf::strings(['a'])->sum() → UnsupportedOperation
testJoin: strings(['a','b'])->join(',') === 'a,b'; integers([1])->join() → UnsupportedOperation
```

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: aggregates`.

---

### Task 7: Slicing, filtering, ordering

**Files:**
- Create: `src/Behaviour/{Slicing,Ordering}Behaviour.php`
- Modify: `ReadableList`, `ListOf`
- Test: `tests/Contract/SlicingOrderingContract.php`

**Interfaces:**
- Produces public methods, all in `ReadableList`, all returning `self`, all `#[\NoDiscard]`:
  - `filter(\Closure)`, `reject(\Closure)`;
  - `transform(\Closure)`: the result is re-coerced via `coerceAll`, so a wrong type → `ElementTypeMismatch`;
  - `sliced(int $offset, ?int $length = null)`: `array_slice` semantics, negative offset allowed;
  - `take(int)`, `takeLast(int)`, `drop(int)`, `dropLast(int)`: a negative count → `InvalidArgument`;
  - `takeWhile(\Closure)`, `dropWhile(\Closure)`;
  - `sorted(\Closure $comparator)`;
  - `sortedBy(\Closure $selector, bool $descending = false)`: stable; selector values compared with `<=>`. Multi-key sorting is done by chaining stable sorts, least significant key first; this goes in the PHPDoc;
  - `reversed()`, `shuffled()`.

- [ ] **Step 1: Failing tests:**

```php
testFilterReindexes: items(a,b,c)->filter(fn ($e) => $e !== a)->toArray() === [b, c]
testFilterWithInternalFunction: ListOf::ofType('int|string', [1,'a'])->filter(is_string(...))->toArray() === ['a']  // Review Focus 1
testQueriesDoNotTouchOriginal: $l = items(a,b); $l->filter(fn () => false); $l->count() === 2
testTransformRecoerces: ListOf::integers([1])->transform(fn ($e) => 'x') → ElementTypeMismatch
testSlicedNegativeOffset: items(a,b,c)->sliced(-2)->toArray() === [b,c]
testTakeDropBounds: take(0) empty; take(9) all; take(-1) → InvalidArgument; drop(9) empty; takeLast(2); dropLast(1)
testTakeWhileDropWhile
testSortedByIsStableAndDescending: equal keys keep input order; descending reverses key order but keeps ties stable
testMultiKeyByChaining: ->sortedBy(name)->sortedBy(score) orders by score then name
testReversedShuffledKeepMembers: shuffled() has the same multiset (sort ids and compare)
testReturnsSameClass: get_class(items(a)->filter(fn () => true)) === get_class(items(a))
```

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: slicing, filtering and ordering`.

---

### Task 8: Set operations

**Files:**
- Create: `src/Behaviour/SetOperationsBehaviour.php`
- Modify: `ReadableList`, `ListOf`
- Test: `tests/Contract/SetOperationsContract.php`

**Interfaces:**
- Consumes: `Internal\HashSet`, `equality()`, `coerceAll` (each `iterable $other` is coerced; an `iterable` may be another `ReadableList`).
- Produces public methods returning `self`, in `ReadableList`:
  - `unique()`: the first occurrence wins;
  - `uniqueBy(\Closure $selector)`: the selector returns array-key;
  - `diff(iterable ...$others)`: keep the elements of `$this` contained in none; duplicates of `$this` are kept;
  - `diffBy(\Closure $selector, iterable ...$others)`;
  - `intersect(iterable ...$others)`: keep the elements contained in all; with no others it returns a copy;
  - `intersectBy(\Closure $selector, iterable ...$others)`;
  - `union(iterable ...$others)`: `unique()` of the concatenation;
  - `merge(iterable ...$others)`: concatenation.

- [ ] **Step 1: Failing tests:**

```php
testUniqueByEquatable: items(Item a/1, Item a/2, Item b/1)->unique() → [a/1, b/1]
testUniqueByIdentityForTags: two equal-named Tag instances both kept
testUniqueFloats: ListOf::floats([0.0, -0.0, NAN, NAN])->unique() has 3 elements (0.0, NAN, NAN)   // Review Focus 3
testDiffKeepsOwnDuplicates: integers([1,1,2])->diff([2]) === [1,1]
testDiffCoercesOthers: integers([1])->diff(['1']) → ElementTypeMismatch
testIntersectAll: integers([1,2,3])->intersect([2,3],[3]) === [3]; intersect() with no args === copy
testUnionAndMerge: integers([1,2])->union([2,3]) === [1,2,3]; merge([2]) === [1,2,2]
testByVariants: uniqueBy(fn (Tag $t) => $t->name) dedupes Tags; diffBy/intersectBy compare selector keys
testAcceptsAnotherList: integers([1,2])->diff(ListOf::integers([2])) === [1]
```

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: set operations`.

---

### Task 9: Structuring and mapping

**Files:**
- Create: `src/Behaviour/{Structuring,Mapping}Behaviour.php`, `src/TypedList.php`
- Modify: `ReadableList`, `ListOf`
- Test: `tests/Contract/StructuringMappingContract.php`

**Interfaces:**
- Produces:
  - `interface TypedList extends ReadableList`, with `public static function fromArray(array $elements): static`, `public static function fromIterable(iterable $elements): static` and `public static function empty(): static`. The element type is fixed by the class. It is implemented by generated, fixture and ODM classes, not by `ListOf`.
  - Public in `ReadableList`:
    - `split(\Closure): array` (`array{self<T>, self<T>}`; matching first);
    - `groupBy(\Closure): array` (`array<array-key, self<T>>`);
    - `chunk(int $size): array` (`list<self<T>>`; size < 1 → `InvalidArgument`);
    - `windowed(int $size, int $step = 1, bool $partial = false): array`;
    - `chunkWhile(\Closure): array`;
    - `keyBy(\Closure): array` (`array<array-key, T>`; the last wins);
    - `mapTo(ElementType|string $type, \Closure $mapper): ListOf`;
    - `flatMapTo(ElementType|string $type, \Closure $mapper): ListOf` (the mapper returns `iterable`);
    - `mapToArray(\Closure): array` (`list<U>`);
    - `mapInto(string $class, \Closure $mapper): TypedList` (`class-string<C of TypedList>` → `$class::fromArray(...)`);
    - `each(\Closure): self` (returns `$this`; a callback returning exactly `false` stops; no `#[\NoDiscard]`);
    - `pipe(\Closure ...$steps): mixed` (each step receives the previous result, the first receives `$this`; with no steps it returns `$this`).

- [ ] **Step 1: Failing tests:**

```php
testSplit; testGroupByPreservesOrderWithinGroups; testChunkLastPartial; testChunkZero → InvalidArgument
testWindowed: integers([1,2,3,4])->windowed(2) → [[1,2],[2,3],[3,4]]; windowed(3, 2, partial: true) → [[1,2,3],[3,4]]
testChunkWhile: integers([1,2,4,5])->chunkWhile(fn ($p, $c) => $c === $p + 1) → [[1,2],[4,5]]
testKeyByLastWins
testMapToChecksTargetType: items(a)->mapTo('int', fn (Item $i) => $i->score)->toArray() === [1]; mapTo('int', fn () => 'x') → ElementTypeMismatch
testFlatMapTo; testMapToArrayUnchecked
testEachStopsOnFalse: counts 1 visit when the callback returns false; returns the same instance
testPipeChains: items(a,b)->pipe(fn ($l) => $l->count(), fn ($n) => $n * 10) === 20
```

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: structuring and mapping`.

---

### Task 10: Immutable variant and the generator-style fixture

**Files:**
- Create: `src/ImmutableList.php`, `src/Behaviour/{Immutable,ImmutableList}Behaviour.php`, `src/Behaviour/GenericImmutableFacade.php`, `tests/Fixture/{ItemList,IntegerList}.php`, `tests/Contract/ImmutableContract.php`, the contract subclasses `tests/Unit/Immutable/ItemList*Test.php` (one per contract), `tests/Unit/ListOf/ListOfImmutableTest.php`
- Modify: `ListOf` (implements `ImmutableList`, uses `ImmutableListBehaviour` + `GenericReadingFacade` + `GenericImmutableFacade`)

**Interfaces:**
- Produces:
  - `interface ImmutableList extends ReadableList`: `without(int ...$indexes): self` (out of range → `IndexOutOfRange`), `withoutIf(\Closure): self`.
  - `trait ImmutableBehaviour`: protected `doWith(mixed ...)`, `doWithPrepended(mixed ...)`, `doWithSet(int, mixed)` (index in `[0, count]`), `doWithInsertedAt(int, mixed)` (`[0, count]`), `doWithoutElement(mixed ...)` (removes the first occurrence of each), `doWithReplaced(mixed $old, mixed $new)` (replaces every occurrence). All return `self`.
  - `trait ImmutableListBehaviour` uses `ListStorage`, `ReadingBehaviour`, `SearchingBehaviour`, `AggregatingBehaviour`, `SlicingBehaviour`, `OrderingBehaviour`, `SetOperationsBehaviour`, `StructuringBehaviour`, `MappingBehaviour`, `ImmutableBehaviour`. It is the only trait a generated immutable class uses.
  - `GenericImmutableFacade`: public `with`, `withPrepended`, `withSet`, `withInsertedAt`, `withoutElement`, `withReplaced` with `mixed` parameters. They coerce, then call `do*`.
  - **Generator-style class (the contract for project 2).** `final class Tests\Fixture\ItemList implements ImmutableList, TypedList`:
    - `use ImmutableListBehaviour;`
    - `elementType()` returns a `static` cached `ElementType::ofClass(Item::class)`;
    - `equality()` returns a cached `new ComparatorEquality([Item::class => static fn (Item $a, Item $b): bool => $a->equals($b)])`;
    - `fromArray(array $elements): static` and `fromIterable(iterable $elements): static` → `new static($elements)`; `empty(): static`; `of(Item ...$elements): static`;
    - typed one-liners: `first(): ?Item`, `last(): ?Item`, `firstOrFail(): Item`, `lastOrFail(): Item`, `get(int $index): ?Item`, `getOrFail(int $index): Item`, `at(int $position): ?Item`, `single(): Item`, `singleOrNull(): ?Item`, `random(): ?Item`, `find(\Closure $predicate): ?Item`, `findLast(\Closure $predicate): ?Item`, `minBy(\Closure $selector): ?Item`, `maxBy(\Closure $selector): ?Item`, `contains(Item $element): bool`, `containsAll(Item ...$elements): bool`, `containsAny(Item ...$elements): bool`, `positionOf(Item $element): ?int`, `with(Item ...$elements): self`, `withPrepended(Item ...$elements): self`, `withSet(int $index, Item $element): self`, `withInsertedAt(int $index, Item $element): self`, `withoutElement(Item ...$elements): self`, `withReplaced(Item $old, Item $new): self`;
    - `#[\NoDiscard]` on each.

    `IntegerList` is the same shape over `int` with `StrictEquality`, plus `use NumericBehaviour;`.

- [ ] **Step 1: Failing tests:**

```php
// ImmutableContract (runs on ListOf and ItemList)
testWithAppendsAndLeavesOriginal: $l = items(a); $m = $l->with(b); $l->count() === 1; $m->toArray() === [a, b]
testWithSetBounds: withSet(1, b) on [a] appends; withSet(5, b) → IndexOutOfRange; withInsertedAt(0, b) prepends
testWithoutIndexes: items(a,b,c)->without(0, 2) === [b]; without(7) → IndexOutOfRange
testWithoutElementRemovesFirstOccurrence: items(a,b,a)->withoutElement(a) === [b, a]
testWithReplacedAll: items(a,b,a)->withReplaced(a, c) === [c, b, c]
testWithoutIfReindexes
// ItemList-specific
testNativeTypeRejectsWrongType: expect \TypeError on (fn () => ItemList::of(new Tag('x')))()
testFromArrayRuntimeChecked: ItemList::fromArray([new Tag('x')]) → ElementTypeMismatch
testQueriesKeepClass: ItemList::of(a)->filter(fn () => true) instanceof ItemList
testIntegerListNumeric: IntegerList::of(1,2)->sum() === 3
testMapIntoTypedList: ListOf::of(Item::class, [a])->mapInto(ItemList::class, fn ($e) => $e) instanceof ItemList
testEveryValueReturningPublicMethodIsNoDiscard: reflect ItemList and ListOf; every public method whose return type is not void, except `each`, has the attribute `NoDiscard`
```

Add a contract subclass for each of `ReadableListContract`, `SearchingContract`, `AggregatingContract`, `SlicingOrderingContract`, `SetOperationsContract`, `StructuringMappingContract` and `ImmutableContract`, with `items()` → `ItemList::fromArray($items)`. `tags()` and `nullableItems()` stay on `ListOf` (the fixture's type is `Item`), and those tests are skipped in fixture subclasses via a `supportsTags()` hook that returns false.

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: immutable variant`.

---

### Task 11: Mutable variant

**Files:**
- Create: `src/MutableList.php`, `src/Behaviour/{Mutable,MutableList}Behaviour.php`, `src/Behaviour/GenericMutableFacade.php`, `src/MutableListOf.php`, `tests/Fixture/MutableItemList.php`, `tests/Contract/MutableContract.php`, contract subclasses for `MutableListOf` and `MutableItemList`
- Modify: `ReadableList`, `ReadingBehaviour` (`toMutable`/`toImmutable`)

**Interfaces:**
- Produces:
  - `interface MutableList extends ReadableList`:
    - `clear(): void`, `sort(\Closure): void`, `sortBy(\Closure, bool $descending = false): void`, `reverse(): void`;
    - `swap(int, int): void`, `move(int $from, int $to): void` (both: out of range → `IndexOutOfRange`);
    - `removeIf(\Closure): int` (removed count);
    - `remove(int $index): mixed` (null when missing), `pop(): mixed`, `shift(): mixed`.
  - `trait MutableBehaviour`: the public methods above. The protected ones are `doPush(mixed ...)`, `doUnshift(mixed ...)`, `doSet(int, mixed)` (`[0, count]`), `doInsertAt(int, mixed)` (`[0, count]`), `doRemove(int): mixed`, `doRemoveElement(mixed): bool` (first occurrence), `doPop()`, `doShift()`.
  - `trait MutableListBehaviour`: like `ImmutableListBehaviour`, with `MutableBehaviour` instead of `ImmutableBehaviour`.
  - `final class MutableListOf implements MutableList`: the same factories as `ListOf`, plus `GenericReadingFacade` + `GenericMutableFacade` (`push`, `unshift`, `set`, `insertAt`, `removeElement`, mixed with coercion).
  - Added to `ReadableList` and `ReadingBehaviour`: `toMutable(): MutableListOf`, `toImmutable(): ListOf`. Both carry over `elementType()` and `equality()` and copy the elements.
  - `MutableItemList` fixture: the same shape as `ItemList`, over `MutableListBehaviour`, with typed `push(Item ...): void`, `unshift(Item ...): void`, `set(int, Item): void`, `insertAt(int, Item): void`, `removeElement(Item): bool`, `remove(int): ?Item`, `pop(): ?Item`, `shift(): ?Item`.

- [ ] **Step 1: Failing tests:**

```php
// MutableContract (MutableListOf, MutableItemList)
testPushUnshiftSetInsert with bounds: set(count) appends; set(count+1) → IndexOutOfRange
testRemoveReindexes: [a,b,c] remove(0) returns a, toArray === [b,c]; remove(9) === null
testRemoveElementFirstOccurrence; testRemoveIfReturnsCount; testPopShiftOnEmptyNull
testSortReverseSwapMove: move(0, 2) on [a,b,c] === [b,c,a]; swap(0, 9) → IndexOutOfRange
testClear
testQueriesReturnNewInstance: $m->filter(...) !== $m; $m unchanged
testIterationIsSnapshot: foreach ($m as $e) { $m->push($e); } terminates; count doubles   // Review Focus 5
testToImmutableAndBack: $m->toImmutable() instanceof ListOf; equal contents; later push on $m does not leak
testPushCoercesOnGeneric: MutableListOf::floats()->push(3) then toArray() === [3.0]   // Review Focus 2
```

Run all earlier contracts on `MutableListOf` and `MutableItemList` too, as in Task 10.

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: mutable variant`.

---

### Task 12: ODM variant (unit level)

**Files:**
- Create: `src/Doctrine/{DoctrineCollectionBehaviour,OdmListBehaviour}.php`, `tests/Fixture/Odm/{Pet,PetCollection}.php`, `tests/Unit/Doctrine/PetCollectionTest.php`, contract subclasses for `PetCollection`

**Interfaces:**
- Consumes: `MutableListBehaviour`, `ElementType::coerce`.
- Produces:
  - **`trait DoctrineCollectionBehaviour`**, which implements the members of `Doctrine\Common\Collections\Collection` that our API lacks, with Doctrine's exact signatures (see the list in spec 7.7):
    - `add(mixed): void` (coerce + push);
    - `set(string|int $key, mixed $value): void` (a string key → `InvalidArgument`, otherwise `doSet`);
    - `remove(string|int $key): mixed` (a string key → null);
    - `removeElement(mixed): bool`, `contains(mixed): bool` (coerce first);
    - `containsKey(string|int): bool`, `get(string|int $key): mixed`, `getKeys(): array`, `getValues(): array`;
    - `key(): int|string|null`, `current(): mixed`, `next(): mixed` (an internal pointer `private int $position = 0`, reset by `first()`/`last()` as in `ArrayCollection`);
    - `slice(int $offset, int|null $length = null): array`;
    - `exists(\Closure): bool` / `forAll(\Closure): bool` / `findFirst(\Closure): mixed` (callback `(key, element)`);
    - `partition(\Closure): array` (`(key, element)`, returns two `self`);
    - `map(\Closure): \Doctrine\Common\Collections\ArrayCollection`;
    - `indexOf(mixed): int|false`;
    - `offsetSet(mixed $offset, mixed $value): void` (null offset → `add`), `offsetUnset(mixed): void`.
  - **`trait OdmListBehaviour`:** `use MutableListBehaviour, DoctrineCollectionBehaviour { DoctrineCollectionBehaviour::offsetSet insteadof MutableListBehaviour; DoctrineCollectionBehaviour::offsetUnset insteadof MutableListBehaviour; DoctrineCollectionBehaviour::remove insteadof MutableListBehaviour; }`. Doctrine's `remove(string|int)` handles int keys exactly like `MutableBehaviour::remove(int)`. No other public names collide: our element-typed methods are protected `do*`.
  - **Fixture `class Tests\Fixture\Odm\PetCollection implements Collection, MutableList, TypedList`** (not final; constructor inherited from `ListStorage` with default `[]`; no object-valued defaults). It has:
    - the typed one-liners of `MutableItemList`, except those Doctrine names forbid narrowing: `contains`/`removeElement`/`set`/`remove`/`get` keep Doctrine's parameter types;
    - narrowed returns where allowed: `first(): ?Item`, `get(string|int $key): ?Item`, `remove(string|int $key): ?Item`.

    The element is the embeddable fixture `Odm\Pet` (`#[ODM\EmbeddedDocument]`, with id and name, `Equatable` by name), so that Task 14 can persist it. The contracts' `items()` hook maps each `Item` to a `Pet` (id → name) for this subclass.

- [ ] **Step 1: Failing tests:**

```php
testIsDoctrineCollection: new PetCollection() instanceof Collection
testAddChecksType: (new PetCollection())->add(new Tag('x')) → ElementTypeMismatch
testRemoveReindexesUnlikeArrayCollection: remove(0) on [a,b] → getKeys() === [0]
testSetStringKeyRejected: set('a', pet) → InvalidArgument
testFirstOnEmptyIsNullNotFalse
testDoctrineCallbackOrder: exists(fn ($k, $e) => $k === 1); partition((k, e)) returns [PetCollection, PetCollection]
testMapReturnsArrayCollection: map(fn (Pet $p) => $p->name) instanceof ArrayCollection
testOurNamesKeepOurSemantics: positionOf(absent) === null while indexOf(absent) === false
testOffsetSetAppendsAndUnsetReindexes
testPointerMethods: first(); next() === second; key() === 1; current() === second
testNoStaticReturnsOnPublicApi: reflection over PetCollection — no public instance method declares return type `static`
```

Run every contract on `PetCollection` (Mutable and the earlier ones).

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`. Then `deptrac`: only `src/Doctrine` touches `Doctrine\*`.
- [ ] **Step 5:** Commit: `feat: Doctrine ODM collection variant`.

---

### Task 13: Pipe functions

**Files:**
- Modify: `src/Pipe/functions.php`
- Test: `tests/Unit/Pipe/FunctionsTest.php`

**Interfaces:**
- Consumes: `ReadableList`, `ImmutableList`.
- Produces, in namespace `MSSTC4PHP\DtoCollection\Pipe`, one function per public method of `ReadableList` and `ImmutableList` whose parameters don't name `T`. Each has the same parameters as the method and returns `\Closure(ReadableList<T>): <method return>`. Each is a one-line arrow function that calls the method, and each is `#[\NoDiscard]`.
  - Element-parameter methods (`contains`, `positionOf`, `with*` taking `T`) are omitted: PHP cannot type them on an interface. This is documented in `docs/pipe.md`.
  - Mutators are omitted (spec 7.8).
  - Each function is guarded with `if (!\function_exists(...))`.

- [ ] **Step 1: Failing tests:**

```php
testPipeComposition: ListOf::integers([5,1,4])->pipe(filter(fn ($e) => $e > 1), sortedBy(fn ($e) => $e), take(1))->toArray() === [4]
testEveryInterfaceMethodHasAFunction: reflect ReadableList+ImmutableList; for each method without a parameter named element/elements/old/new, function_exists('MSSTC4PHP\DtoCollection\Pipe\\' . name)
testFunctionsAreNoDiscard: ReflectionFunction attributes contain NoDiscard
```

- [ ] **Steps 2–4:** fail → implement → pass; `make fix && make check`.
- [ ] **Step 5:** Commit: `feat: pipe functions`.

---

### Task 14: ODM integration against MongoDB

**Files:**
- Create: `tests/Integration/Odm/OdmTestCase.php`, `tests/Integration/Odm/{EmbedManyTest,ReferenceManyTest}.php`, `tests/Fixture/Odm/{Owner,OwnerCollection,Holder}.php`
- Modify: `docs/odm.md` (created here)

**Interfaces:**
- Consumes: `PetCollection`.
- Produces:
  - `OdmTestCase`: it skips with the message `Set DTO_COLLECTION_MONGODB_URI to run ODM integration tests` when the env var is unset. It builds a `DocumentManager` with attribute metadata over `tests/Fixture/Odm`, `persistentCollectionDir` = `var/odm/collections`, `AUTOGENERATE_EVAL`, a database named `dto_collection_test_<pid>` dropped in `tearDown`.
  - Fixtures:
    - `Owner` (`#[ODM\Document]`) with `#[ODM\EmbedMany(targetDocument: Pet::class, collectionClass: PetCollection::class, strategy: 'setArray')] private PetCollection $pets`;
    - a second field with `strategy: 'atomicSetArray'`;
    - `Holder` with `#[ODM\ReferenceMany(targetDocument: Owner::class, collectionClass: OwnerCollection::class, strategy: 'setArray')]`.

- [ ] **Step 1: Failing tests:**

```php
testLoadedFieldIsOurClass: persist Owner with 2 pets, clear, find → $owner->pets() instanceof PetCollection
testQueriesOnLoadedCollection: ->pets()->filter(fn (Pet $p) => true) instanceof PetCollection; ->sortedBy(name) works   // Review Focus 4
testTypedMutatorPersists: loaded->pets()->push(new Pet(...)); flush; clear; reload → 3 pets
testSortPersistsOrder: sortBy(name, descending: true); flush; reload → order kept
testRemoveKeepsArrayShapeInMongo: remove(0); flush; raw MongoDB document's `pets` is a BSON array (not an object), for both setArray and atomicSetArray
testReferenceManyRoundTrip
testNullElements: try to persist a nullable collection fixture with a null element — record the outcome. If ODM drops or rejects nulls, assert that and set the documented rule "ODM variant: no nullable elements" in docs/odm.md and in the TypedCollection docblock (spec 6.3 risk)
```

- [ ] **Step 2:** Run `make test-odm`: FAIL.
- [ ] **Step 3:** Fix whatever the real proxy reveals in `src/Doctrine`. Write `docs/odm.md`: mapping example, `setArray`/`atomicSetArray`, the non-`final` and zero-argument-constructor rules, `first()` returning null instead of false, and the null-element result.
- [ ] **Step 4:** Run `make test-odm`: PASS. Then `make check`.
- [ ] **Step 5:** Commit: `test: ODM integration against MongoDB`.

---

### Task 15: CI, mutation testing, release readiness, documentation

**Files:**
- Create: `.github/workflows/ci.yml`, `.roave-backward-compatibility-check.xml`, `README.md`, `docs/{getting-started,generic-lists,writing-a-typed-collection,equality,methods,pipe,odm}.md`, `CHANGELOG.md`, `.claude/docs/{README,architecture,domain-model,integrations,known-issues,conventions}.md`
- Modify: `infection.json5` (equivalent-mutant ignores, each with a reason)

- [ ] **Step 1:** `ci.yml`: the jobs `check` (PHP 8.4, `make check`) and `test`, a matrix of PHP 8.1, 8.2, 8.3 and 8.4 with `shivammathur/setup-php`, extension `mongodb`, a `mongo:7` service and `DTO_COLLECTION_MONGODB_URI` set. `test` runs `vendor/bin/phpunit --fail-on-skipped`, and an `infection` job runs on 8.4 with `--threads=4`. On 8.1 and 8.2, install with `doctrine/collections ^2` (Composer resolves it automatically).
- [ ] **Step 2:** Run `make infection`. Kill every surviving mutant with a test. Only a provably equivalent mutant goes into `infection.json5`, with a reason. Expected: MSI 100 % (floor 99).
- [ ] **Step 3:** Write the docs:
  - `writing-a-typed-collection.md` is the facade tables from Tasks 10–12. It is the contract for `dto-collection-generator`;
  - `equality.md` covers the strategies, the O(n²) note, `NAN` and `-0.0`;
  - `methods.md` is a reference grouped like spec 7.4–7.7.

  Write `CHANGELOG.md`: `## 1.0.0 - unreleased`. Write `.claude/docs/` per the global CLAUDE.md, including the ODM `static` return trap in `known-issues.md`.
- [ ] **Step 4:** Run `make verify && make test-odm && make infection`. Expected: all green.
- [ ] **Step 5:** Run `/acc:code-review high` on the branch and fix every finding, then rerun until clean, as the global CLAUDE.md requires.
- [ ] **Step 6:** Commit: `docs: user documentation, CI and release readiness`. Don't push or tag without the owner.
