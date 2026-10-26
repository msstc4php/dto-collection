<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture;

use MSSTC4PHP\DtoCollection\Equatable;

final class Cat implements Equatable
{
    public function __construct(public readonly string $name)
    {
    }

    public function equals(object $other): bool
    {
        return $other instanceof self && $other->name === $this->name;
    }
}
