<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture;

final class Item
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
