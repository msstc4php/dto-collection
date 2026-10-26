# dto-collection

Typed lists for PHP 8.1+. The element type is a native type hint of the list class, so PHP
itself rejects a wrong element:

```php
$users->push($user);        // push(User ...$elements): void
$first = $users->first();   // first(): ?User
$users->push('alice');      // TypeError
```

PHP has no generic arrays. This library supplies the behaviour as traits, and each list class
adds a few one-line methods that carry the element type. You write that class by hand or let
`dto-collection-generator` write it. Without any class of your own, `ListOf` and
`MutableListOf` check types at runtime.

- Three variants: **immutable** (`with*`/`without*` return a copy), **mutable** (in-place
  mutators return `void`), and **Doctrine MongoDB ODM** (usable as `collectionClass` of
  `EmbedMany`/`ReferenceMany`).
- Keys are always `0..n-1`. Every operation reindexes.
- Element types: `int`, `string`, `float`, `bool`, classes and interfaces, `?T`, unions `A|B`,
  intersections `A&B`.
- About 70 methods: the useful parts of lodash-php and voku/Arrayy, without their aliases and
  bugs, plus pipe functions for PHP 8.5's `|>`.
- Elements are compared with `===`, or with `equals()` for classes implementing `Equatable`.

## Installation

```bash
composer require msstc4php/dto-collection
```

Version 1.x supports PHP 8.1–8.4. Version 2.x, for PHP 8.5+, has the same API. For the ODM
variant, also install `doctrine/mongodb-odm` (`^2.10`).

## Quick start

```php
use MSSTC4PHP\DtoCollection\ListOf;

$scores = ListOf::integers([3, 1, 2]);
$scores->sum();                                   // 6
$scores->sortedBy(fn (int $score) => $score)->toArray();  // [1, 2, 3]

$users = ListOf::of(User::class, $repository->findAll());
$admins = $users->filter(fn (User $user) => $user->isAdmin());
$names = $admins->mapToArray(fn (User $user) => $user->name());
$byTeam = $users->groupBy(fn (User $user) => $user->team());   // array<string, ListOf<User>>

$users->with('alice');   // ElementTypeMismatch: Element #0 must be User, string given
```

## Documentation

- [Getting started](docs/getting-started.md)
- [Generic lists: `ListOf`, `MutableListOf`, `ElementType`](docs/generic-lists.md)
- [Writing a typed collection class](docs/writing-a-typed-collection.md), which is also the
  contract for `dto-collection-generator`
- [Methods](docs/methods.md)
- [Equality](docs/equality.md)
- [Pipe functions](docs/pipe.md)
- [Doctrine MongoDB ODM](docs/odm.md)
- [Changelog](CHANGELOG.md)

## Development

```bash
make install   # package and tool dependencies
make verify    # PHPStan, CS-Fixer, Rector, deptrac, PHP 8.1 lint, tests on local PHP and 8.1
make test-odm  # ODM integration tests against MongoDB in Docker (port 27018)
make infection # mutation testing
```

## License

MIT
