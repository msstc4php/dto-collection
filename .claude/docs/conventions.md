# Conventions

- Element-typed trait methods: `protected do<Name>()`; public typed one-liners in the class.
- Methods returning the list: native `self`, PHPDoc `@return static`; never native `static`.
- `#[NoDiscard]` on value-returning public methods except `each`, `remove`, `removeElement`,
  `removeIf`, `pop`, `shift`, `next`, and Doctrine's untyped `add`/`set`/`clear`/`offsetSet`/
  `offsetUnset` — pinned by `tests/Unit/PublicApiTest.php`.
- Contract suites: `tests/Contract/*Contract` extend `ListContractCase`, whose hooks return a
  `@phpstan-type` union of the classes under test; each variant has a `*Lists` hook trait and
  one subclass per contract. Fixture variants keep `tags()`/`nullableItems()` on the generic
  lists (no skips: CI runs `--fail-on-skipped`).
- Wrong-type inputs in tests carry `// Reason: the wrong element type is the input under test.`
  above the `@phpstan-ignore`.
- Generator-style fixtures (`ItemList`, `MutableItemList`, `PetCollection`) are the reference for
  `dto-collection-generator`; change `docs/writing-a-typed-collection.md` with them.
- Mutation testing: `make infection` (4 threads, unit suite), MSI 100 %; equivalent mutants go
  to `infection.json5` with a reason.
- Check doctrine/collections 3 and the lowest dependencies in a scratch copy before changing
  the ODM signatures (`composer config platform.php 8.4.17 && composer update`, resp.
  `composer update --prefer-lowest`).

## Repository files follow dto-generator

`SECURITY.md` (with a "what it trusts" section), README badges plus Security/License
sections, a Keep a Changelog `CHANGELOG.md` with `[x.y.z]` headings and compare links, and
the CI jobs with the Composer cache steps are kept as in `../dto-generator`. Deliberate
differences: PHP 8.1 floor, `--prefer-lowest` only on 8.1, ParallelConfig(4), no Docker image.
