# Security Policy

## Supported Versions

The package follows semantic versioning. Security fixes are released for the latest
minor on each supported major.

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## What the library trusts

- **It does no I/O.** No file, network or environment access; the only code it runs is
  the callbacks and comparators you pass and the `equals()` of elements that implement
  `Equatable`.
- **Element types are enforced, not assumed.** The concrete list classes take elements
  through native type hints; the generic `ListOf` / `MutableListOf` and the ODM variant
  check each element against the list's element type and throw on a mismatch.

## Reporting a Vulnerability

Please report vulnerabilities privately to maxim.shamaev@gmail.com rather than
opening a public issue. You will get an answer within a week.
