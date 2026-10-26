<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture\Odm;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MSSTC4PHP\DtoCollection\Equatable;

#[ODM\EmbeddedDocument]
final class Pet implements Equatable
{
    public function __construct(
        #[ODM\Field(type: 'string')]
        private string $name,
        #[ODM\Field(type: 'int')]
        private int $age = 0,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function age(): int
    {
        return $this->age;
    }

    public function equals(object $other): bool
    {
        return $other instanceof self && $other->name === $this->name;
    }
}
