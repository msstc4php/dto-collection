<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use MSSTC4PHP\DtoCollection\Exception\ElementTypeMismatch;
use MSSTC4PHP\DtoCollection\Exception\InvalidArgument;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\Pet;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Odm\PetCollection;
use MSSTC4PHP\DtoCollection\Tests\Fixture\OdmItemCollection;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Tag;
use PHPUnit\Framework\TestCase;

final class PetCollectionTest extends TestCase
{
    private Pet $rex;

    private Pet $tom;

    protected function setUp(): void
    {
        $this->rex = new Pet('rex', 3);
        $this->tom = new Pet('tom', 5);
    }

    public function testIsDoctrineCollection(): void
    {
        $pets = new PetCollection();

        self::assertTrue($pets->isEmpty());
    }

    public function testOdmCanGenerateItsPersistentSubclass(): void
    {
        $command = sprintf('%s -d error_reporting=-1 -d display_errors=stderr %s %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(__DIR__ . '/generate-persistent-collection.php'), escapeshellarg(PetCollection::class) . ' ' . escapeshellarg(OdmItemCollection::class));
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode("\n", $output));
        self::assertCount(2, $output, implode("\n", $output));
    }

    public function testAddChecksType(): void
    {
        $this->expectException(ElementTypeMismatch::class);
        $this->expectExceptionMessage('Element #0 must be ' . Pet::class . ', ' . Tag::class . ' given');

        // Reason: the wrong element type is the input under test.
        (new PetCollection())->add(new Tag('x')); // @phpstan-ignore argument.type
    }

    public function testMixedMethodsCheckType(): void
    {
        $pets = PetCollection::of($this->rex);
        $calls = [
            'contains' => static fn (): bool => $pets->contains('rex'),
            'indexOf' => static fn (): int|string|false => $pets->indexOf('rex'),
            // Reason: the wrong element type is the input under test.
            'removeElement' => static fn (): bool => $pets->removeElement('rex'), // @phpstan-ignore argument.type
            'set' => static function () use ($pets): void {
                // Reason: the wrong element type is the input under test.
                $pets->set(0, 'rex'); // @phpstan-ignore argument.type
            },
            'offsetSet' => static function () use ($pets): void {
                // Reason: the wrong element type is the input under test.
                $pets[] = 'rex'; // @phpstan-ignore offsetAssign.valueType
            },
        ];
        foreach ($calls as $method => $call) {
            try {
                $call();
                self::fail($method . '() accepted a string');
            } catch (ElementTypeMismatch) {
                self::assertSame([$this->rex], $pets->toArray());
            }
        }
    }

    public function testRemoveReindexesUnlikeArrayCollection(): void
    {
        $pets = PetCollection::of($this->rex, $this->tom);

        self::assertSame($this->rex, $pets->remove(0));
        self::assertSame([0], $pets->getKeys());
        self::assertSame([$this->tom], $pets->getValues());
        self::assertNull($pets->remove('a'));
        self::assertNull($pets->remove(5));
    }

    public function testSetStringKeyRejected(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('A list takes integer keys only, "a" given');

        (new PetCollection())->set('a', $this->rex);
    }

    public function testSetAndGet(): void
    {
        $pets = PetCollection::of($this->rex);
        $pets->set(1, $this->tom);
        $pets->set(0, $this->tom);

        self::assertSame([$this->tom, $this->tom], $pets->toArray());
        self::assertSame($this->tom, $pets->get(1));
        self::assertNull($pets->get('1'));
        self::assertNull($pets->get(2));
        self::assertTrue($pets->containsKey(1));
        self::assertFalse($pets->containsKey(2));
        self::assertFalse($pets->containsKey('0'));
    }

    public function testFirstOnEmptyIsNullButPointerMethodsFollowDoctrine(): void
    {
        $pets = new PetCollection();

        self::assertNull($pets->first());
        self::assertNull($pets->last());
        self::assertNull($pets->key());
        self::assertFalse($pets->current());
        self::assertFalse($pets->next());
    }

    public function testDoctrineCallbackOrder(): void
    {
        $pets = PetCollection::of($this->rex, $this->tom);

        self::assertTrue($pets->exists(static fn (int $key, Pet $pet): bool => $key === 1));
        self::assertFalse($pets->exists(static fn (int $key, Pet $pet): bool => $key === 2));
        self::assertTrue($pets->forAll(static fn (int $key, Pet $pet): bool => $key < 2));
        self::assertFalse($pets->forAll(static fn (int $key, Pet $pet): bool => $key < 1));
        self::assertSame($this->tom, $pets->findFirst(static fn (int $key, Pet $pet): bool => $pet->age() > 4));
        self::assertNull($pets->findFirst(static fn (int $key, Pet $pet): bool => false));

        [$young, $old] = $pets->partition(static fn (int $key, Pet $pet): bool => $pet->age() < 4);
        self::assertInstanceOf(PetCollection::class, $young);
        self::assertSame([$this->rex], $young->toArray());
        self::assertSame([$this->tom], $old->toArray());
    }

    public function testFilterReduceSlice(): void
    {
        $pets = PetCollection::of($this->rex, $this->tom);

        self::assertSame([$this->tom], $pets->filter(static fn (Pet $pet): bool => $pet->age() > 4)->toArray());
        self::assertSame([$this->rex], $pets->filter(static fn (Pet $pet, int $index): bool => $index === 0)->toArray());
        self::assertSame(8, $pets->reduce(static fn (int $carry, Pet $pet): int => $carry + $pet->age(), 0));
        self::assertSame([$this->tom], $pets->slice(1));
        self::assertSame([$this->rex], $pets->slice(0, 1));
    }

    public function testMapReturnsArrayCollection(): void
    {
        $names = PetCollection::of($this->rex, $this->tom)->map(static fn (Pet $pet): string => $pet->name());

        self::assertInstanceOf(ArrayCollection::class, $names);
        self::assertSame(['rex', 'tom'], $names->toArray());
    }

    public function testOurNamesKeepOurSemantics(): void
    {
        $pets = PetCollection::of($this->rex);

        self::assertNull($pets->positionOf($this->tom));
        self::assertFalse($pets->indexOf($this->tom));
        self::assertSame(0, $pets->indexOf(new Pet('rex')));
        self::assertTrue($pets->contains(new Pet('rex')));
        self::assertTrue($pets->removeElement(new Pet('rex')));
        self::assertFalse($pets->removeElement(new Pet('rex')));
    }

    public function testOffsetSetAppendsAndUnsetReindexes(): void
    {
        $pets = new PetCollection();
        $pets[] = $this->rex;
        $pets[] = $this->tom;
        $pets[0] = $this->tom;

        self::assertSame([$this->tom, $this->tom], $pets->toArray());
        self::assertTrue(isset($pets[1]));
        self::assertFalse(isset($pets[2]));
        self::assertSame($this->tom, $pets[1]);
        self::assertNull($pets[2]);

        // Reason: a string key is the input under test; Doctrine allows it, the list ignores it.
        unset($pets[0], $pets['x']); // @phpstan-ignore unset.offset
        self::assertSame([$this->tom], $pets->toArray());
        self::assertSame([0], $pets->getKeys());
    }

    public function testOffsetSetRejectsNonScalarKeys(): void
    {
        $pets = new PetCollection();

        $this->expectException(InvalidArgument::class);
        // Reason: a float key is the input under test.
        $pets[1.5] = $this->rex; // @phpstan-ignore offsetAssign.dimType
    }

    public function testPointerMethods(): void
    {
        $pets = PetCollection::of($this->rex, $this->tom);

        self::assertSame($this->rex, $pets->first());
        self::assertSame(0, $pets->key());
        self::assertSame($this->tom, $pets->next());
        self::assertSame(1, $pets->key());
        self::assertSame($this->tom, $pets->current());
        self::assertFalse($pets->next());
        self::assertNull($pets->key());
        self::assertFalse($pets->current());
        self::assertSame($this->tom, $pets->last());
        self::assertSame(1, $pets->key());
        self::assertSame($this->rex, $pets->first());
        self::assertSame($this->rex, $pets->current());
    }

    public function testTypedFacade(): void
    {
        $pets = new PetCollection([$this->rex]);
        $pets->push($this->tom);
        $pets->unshift($this->tom);
        $pets->insertAt(1, $this->rex);

        self::assertSame([$this->tom, $this->rex, $this->rex, $this->tom], $pets->toArray());
        self::assertSame($this->tom, $pets->pop());
        self::assertSame($this->tom, $pets->shift());
        self::assertSame(0, $pets->positionOf(new Pet('rex')));
        self::assertSame($this->rex, $pets->firstOrFail());
        self::assertCount(2, $pets);
        self::assertSame([$this->rex, $this->rex], iterator_to_array($pets));
    }

    public function testAddAppends(): void
    {
        $pets = new PetCollection();
        $pets->add($this->rex);
        $pets->add($this->tom);

        self::assertSame([$this->rex, $this->tom], $pets->toArray());
        self::assertSame([$this->rex, $this->tom], PetCollection::fromIterable(new ArrayCollection([$this->rex, $this->tom]))->toArray());
    }

    public function testMatchingKeepsTheClassAndReindexes(): void
    {
        $pets = PetCollection::of($this->rex, $this->tom, new Pet('ada', 9));
        $criteria = Criteria::create()->where(Criteria::expr()->gt('age', 4))->orderBy(['age' => Order::Descending])->setMaxResults(1);

        $matching = $pets->matching($criteria);

        self::assertInstanceOf(PetCollection::class, $matching);
        self::assertEquals([new Pet('ada', 9)], $matching->toArray());
        self::assertSame([0], array_keys($matching->toArray()));
    }
}
