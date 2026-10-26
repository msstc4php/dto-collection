<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture\Odm;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'owners')]
class Owner
{
    #[ODM\Id]
    // Reason: ODM assigns the id by reflection on persist; PHPStan sees no assignment.
    private ?string $id = null; // @phpstan-ignore property.unusedType

    #[ODM\EmbedMany(strategy: 'setArray', targetDocument: Pet::class, collectionClass: PetCollection::class)]
    private PetCollection $pets;

    #[ODM\EmbedMany(strategy: 'atomicSetArray', targetDocument: Pet::class, collectionClass: PetCollection::class)]
    private PetCollection $atomicPets;

    #[ODM\EmbedMany(strategy: 'setArray', targetDocument: Pet::class, collectionClass: NullablePetCollection::class)]
    private NullablePetCollection $maybePets;

    public function __construct(
        #[ODM\Field(type: 'string')]
        private string $name,
        Pet ...$pets,
    ) {
        $this->pets = PetCollection::of(...$pets);
        $this->atomicPets = PetCollection::of(...$pets);
        $this->maybePets = new NullablePetCollection();
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function pets(): PetCollection
    {
        return $this->pets;
    }

    public function atomicPets(): PetCollection
    {
        return $this->atomicPets;
    }

    public function maybePets(): NullablePetCollection
    {
        return $this->maybePets;
    }
}
