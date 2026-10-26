# Known issues and traps

- **PHPStan and the `if`-declared trait:** inline `@phpstan-ignore` inside
  `src/Doctrine/DoctrineCollectionBehaviour.php` is never collected; deliberate divergences live
  in `phpstan.dist.neon` `ignoreErrors` (path + identifier + reason; v2-only ones
  `reportUnmatched: false`). PHPStan analyses the v3 branch even when collections 2 is installed.
- **Fixers vs the ODM shells:** Rector adds `: void/: bool/: int` and drops PHPDoc,
  PHP-CS-Fixer's `void_return` adds `: void` → the shell file is excluded from both, and
  `void_return` is off globally (Rector covers bodies).
- **`class_exists()` of a lower-cased name** succeeds only for already-loaded classes (PSR-4 on
  a case-sensitive FS) — tests must not depend on it (order-dependent failure seen in Task 3).
- **Closure types in PHPStan:** `Closure(never, never)` makes PHPStan infer `never` inside
  closure literals; `Closure(object, object)` rejects typed literals. Hence
  `ComparatorEquality::of()` (template per class) and untyped `pipe(Closure ...$steps)`.
- **PHPStan enforces `#[NoDiscard]`** (`*.resultDiscarded`): tests assign results of calls that
  are expected to throw.
- **Rector `RemoveDuplicatedReturnSelfDocblockRector`** is skipped: `@return static` over native
  `self` is how queries keep the concrete class for PHPStan.
- **Lowest deps on PHP 8.4:** old proxy-manager-lts emits an implicit-nullable deprecation in its
  own generated code → CI checks the dependency floor on PHP 8.1 only.
- **Sandbox:** Docker targets (`lint-81`, `test-81`, `test-odm`) and `.git/config` writes need
  the sandbox disabled; unsandboxed `$TMPDIR` differs (`/tmp`).
