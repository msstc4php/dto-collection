<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Types;

use ArrayAccess;
use Countable;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Item;
use Traversable;

use function PHPStan\Testing\assertType;

// Analysed by PHPStan, never run: pins the types the generated collections rely on.
assertType('MSSTC4PHP\DtoCollection\ElementType<Countable&Traversable>', ElementType::ofClass(Countable::class)->andInstanceOf(Traversable::class));
assertType('MSSTC4PHP\DtoCollection\ElementType<ArrayAccess&Countable&Traversable>', ElementType::ofClass(Countable::class)->andInstanceOf(Traversable::class)->andInstanceOf(ArrayAccess::class));
assertType('MSSTC4PHP\DtoCollection\ElementType<(Countable&Traversable)|null>', ElementType::ofClass(Countable::class)->andInstanceOf(Traversable::class)->nullable());
assertType('MSSTC4PHP\DtoCollection\ElementType<(Countable&Traversable)|MSSTC4PHP\DtoCollection\Tests\Fixture\Item>', ElementType::union(ElementType::ofClass(Countable::class)->andInstanceOf(Traversable::class), ElementType::ofClass(Item::class)));
