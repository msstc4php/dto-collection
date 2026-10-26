<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Integration\Odm;

use Doctrine\Common\Collections\Criteria;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\Owner;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\Pet;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\PetCollection;
use TypeError;

final class EmbedManyTest extends OdmTestCase
{
    public function testLoadedFieldIsOurClass(): void
    {
        $id = $this->persistedOwner();

        $owner = $this->load($id);

        // ODM's persistent subclass of our class, not ArrayCollection or PersistentCollection.
        self::assertSame(PetCollection::class, get_parent_class($owner->pets()));
        self::assertSame(['rex', 'tom'], $owner->pets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
        self::assertSame(['rex', 'tom'], $owner->atomicPets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
    }

    public function testQueriesOnLoadedCollection(): void
    {
        $owner = $this->load($this->persistedOwner());

        $old = $owner->pets()->filter(static fn (Pet $pet): bool => $pet->age() > 4);
        $sorted = $owner->pets()->sortedBy(static fn (Pet $pet): int => $pet->age(), descending: true);

        self::assertInstanceOf(PetCollection::class, $old);
        self::assertSame(['tom'], $old->mapToArray(static fn (Pet $pet): string => $pet->name()));
        self::assertSame(['tom', 'rex'], $sorted->mapToArray(static fn (Pet $pet): string => $pet->name()));
        self::assertSame('rex', $owner->pets()->firstOrFail()->name());
        self::assertSame(1, $owner->pets()->positionOf(new Pet('tom')));
        self::assertTrue($owner->pets()->any(static fn (Pet $pet): bool => $pet->name() === 'tom'));
    }

    public function testTypedMutatorPersists(): void
    {
        $id = $this->persistedOwner();
        $owner = $this->load($id);
        $owner->pets()->push(new Pet('ada', 1));
        $owner->atomicPets()->push(new Pet('ada', 1));
        $this->flushAndClear();

        $reloaded = $this->load($id);

        self::assertSame(['rex', 'tom', 'ada'], $reloaded->pets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
        self::assertSame(['rex', 'tom', 'ada'], $reloaded->atomicPets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
    }

    public function testDoctrineMutatorPersists(): void
    {
        $id = $this->persistedOwner();
        $owner = $this->load($id);
        $owner->pets()->add(new Pet('ada', 1));
        $owner->pets()->removeElement(new Pet('rex'));
        $this->flushAndClear();

        self::assertSame(['tom', 'ada'], $this->load($id)->pets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
    }

    public function testSortPersistsOrder(): void
    {
        $id = $this->persistedOwner();
        $owner = $this->load($id);
        $owner->pets()->sortBy(static fn (Pet $pet): string => $pet->name(), descending: true);
        $owner->atomicPets()->reverse();
        $this->flushAndClear();

        $reloaded = $this->load($id);

        self::assertSame(['tom', 'rex'], $reloaded->pets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
        self::assertSame(['tom', 'rex'], $reloaded->atomicPets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
    }

    public function testRemoveKeepsArrayShapeInMongo(): void
    {
        $id = $this->persistedOwner();
        $owner = $this->load($id);
        $owner->pets()->remove(0);
        $owner->atomicPets()->remove(0);
        $this->flushAndClear();

        foreach (['pets', 'atomicPets'] as $field) {
            $stored = $this->rawField('owners', $id, $field);
            self::assertIsArray($stored);
            self::assertTrue(array_is_list($stored), $field . ' is stored as a BSON object, not an array');
            self::assertCount(1, $stored);
            self::assertSame(['name' => 'tom', 'age' => 5], $stored[0]);
        }
        self::assertSame(['tom'], $this->load($id)->pets()->mapToArray(static fn (Pet $pet): string => $pet->name()));
    }

    public function testMatchingOnLoadedCollection(): void
    {
        $owner = $this->load($this->persistedOwner());

        $old = $owner->pets()->matching(Criteria::create()->where(Criteria::expr()->gt('age', 4)));

        self::assertSame(['tom'], $old->mapToArray(static fn (Pet $pet): string => $pet->name()));
    }

    public function testClearPersists(): void
    {
        $id = $this->persistedOwner();
        $this->load($id)->pets()->clear();
        $this->flushAndClear();

        self::assertTrue($this->load($id)->pets()->isEmpty());
    }

    public function testOdmRejectsNullElements(): void
    {
        $owner = new Owner('ann');
        $owner->maybePets()->push(null, new Pet('rex', 3));

        // ODM 2.17 fails in UnitOfWork::getParentAssociation(); hence no nullable element types for the ODM variant.
        $this->expectException(TypeError::class);
        $this->documents->persist($owner);
    }

    private function persistedOwner(): string
    {
        $owner = new Owner('ann', new Pet('rex', 3), new Pet('tom', 5));
        $this->documents->persist($owner);
        $this->flushAndClear();
        $id = $owner->id();
        self::assertIsString($id);

        return $id;
    }

    private function load(string $id): Owner
    {
        $owner = $this->documents->find(Owner::class, $id);
        self::assertInstanceOf(Owner::class, $owner);

        return $owner;
    }
}
