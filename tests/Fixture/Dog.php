<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture;

final class Dog
{
    public function __construct(public readonly string $name)
    {
    }
}
