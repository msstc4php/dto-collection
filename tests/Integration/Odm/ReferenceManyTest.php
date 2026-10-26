<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Integration\Odm;

use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\Holder;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\Owner;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\OwnerCollection;

final class ReferenceManyTest extends OdmTestCase
{
    public function testReferenceManyRoundTrip(): void
    {
        $ann = new Owner('ann');
        $bob = new Owner('bob');
        $holder = new Holder($ann, $bob);
        $this->documents->persist($ann);
        $this->documents->persist($bob);
        $this->documents->persist($holder);
        $this->flushAndClear();
        $id = $holder->id();
        self::assertIsString($id);

        $loaded = $this->documents->find(Holder::class, $id);
        self::assertInstanceOf(Holder::class, $loaded);
        self::assertSame(OwnerCollection::class, get_parent_class($loaded->owners()));
        self::assertSame(['ann', 'bob'], $loaded->owners()->mapToArray(static fn (Owner $owner): string => $owner->name()));

        $loaded->owners()->pop();
        $this->flushAndClear();

        $stored = $this->rawField('holders', $id, 'owners');
        self::assertIsArray($stored);
        self::assertTrue(array_is_list($stored));
        self::assertCount(1, $stored);
        $reloaded = $this->documents->find(Holder::class, $id);
        self::assertInstanceOf(Holder::class, $reloaded);
        self::assertSame(['ann'], $reloaded->owners()->mapToArray(static fn (Owner $owner): string => $owner->name()));
    }
}
