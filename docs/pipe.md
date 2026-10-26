# Pipe functions

`MSSTC4PHP\DtoCollection\Pipe` has one function per query of `ReadableList` and `ImmutableList`,
plus `count()`. Each takes the method's arguments, under the same names, and returns a closure
over a list:

```php
use function MSSTC4PHP\DtoCollection\Pipe\{filter, sortedBy, take};

// PHP 8.5
$top = $users
    |> filter(fn (User $user) => $user->isActive())
    |> sortedBy(fn (User $user) => $user->score(), descending: true)
    |> take(10);

// PHP 8.1–8.4
$top = $users->pipe(
    filter(fn (User $user) => $user->isActive()),
    sortedBy(fn (User $user) => $user->score(), descending: true),
    take(10),
);
```

A function only calls the method, so the behaviour is the method's. The closure keeps the list's
class at runtime (an `ItemList` stays an `ItemList`). PHPStan sees `ReadableList<T>`, or
`ImmutableList<T>` for `without`/`withoutIf`.

## Not covered

- **Methods that take an element:** `contains`, `positionOf`, `containsAll`, `containsAny` and
  the `with*`/`withoutElement`/`withReplaced` family. An interface cannot type their parameter;
  only the concrete class can. Call the method directly:
  `$users |> fn (Users $list) => $list->with($admin)`.
- **Mutators** (`push`, `sort`, …). They return `void`, so the pipe would have nothing to pass on.
- **`pipe()` itself.**

Every function is `#[\NoDiscard]` and defined only when no function of that name exists yet.
