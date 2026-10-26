<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture\Odm;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'holders')]
class Holder
{
    #[ODM\Id]
    // Reason: ODM assigns the id by reflection on persist; PHPStan sees no assignment.
    private ?string $id = null; // @phpstan-ignore property.unusedType

    #[ODM\ReferenceMany(strategy: 'setArray', targetDocument: Owner::class, collectionClass: OwnerCollection::class)]
    private OwnerCollection $owners;

    public function __construct(Owner ...$owners)
    {
        $this->owners = OwnerCollection::of(...$owners);
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function owners(): OwnerCollection
    {
        return $this->owners;
    }
}
