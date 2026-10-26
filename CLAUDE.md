# CLAUDE.md

Guidance for Claude Code working in this repository. Deep references live
under `.claude/docs/`; this file stays lean.

## What this is

`msstc4php/dto-collection`: typed lists for PHP, where the element type is a
native type hint of the concrete class (`push(User $element)`, `first(): ?User`).
Variants: immutable, mutable, Doctrine MongoDB ODM (`collectionClass`); generic
`ListOf` / `MutableListOf` check types at runtime. Design:
`docs/internal/specs/2026-10-25-dto-collection-design.md`; plan:
`docs/internal/plans/2026-10-25-dto-collection-1x.md`.

Branch `1.x` runs on **PHP 8.1–8.4**: no PHP 8.2+ syntax or functions
(PHPStan `phpVersion: 80100`, `make lint-81`). Line `2.x` (PHP 8.5+) is branched
from `1.x` after 1.0 and receives `1.x` by merge-up.

## Common commands

- `make install`, `make fix`, `make check`, `make test`, `make test-81`.
- `make verify` — the full gate; `make test-odm` — ODM integration against
  MongoDB in Docker (port 27018); `make infection` — mutation testing (keep 100 %).
- `make bc-check` — public API against the last tag.

## Conventions

- Behaviour lives in traits over one `list<mixed>`; a trait method naming the
  element type is `protected do<Name>()`, the concrete class exposes the typed
  public one-liner.
- Methods returning the collection declare native `self`, never `static`
  (ODM's generated subclass copies `static` and returns the inner collection).
- `#[\NoDiscard]` on every value-returning public method except `each()` and
  the mutators `remove`, `removeElement`, `removeIf`, `pop`, `shift`.
- Only `MSSTC4PHP\DtoCollection\Doctrine\*` may reference `Doctrine\*` (deptrac).
- Full names in the public API (`integers`, `average`).
- Code comments in English, only the non-obvious "why".
- Every change: tests first, `make fix`, `make verify`, `make infection`.
