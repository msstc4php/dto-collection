<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture;

use MSSTC4PHP\DtoCollection\Equatable;

final class Item implements Equatable
{
    public function __construct(
        public readonly string $id,
        public readonly int $score,
    ) {
    }

    public function equals(object $other): bool
    {
        return $other instanceof self && $other->id === $this->id;
    }
}
