<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Doctrine;

use ArrayIterator;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\Common\Collections\Selectable;
use NoDiscard;
use ReturnTypeWillChange;
use Traversable;

/*
 * Doctrine ODM's generated persistent subclass redeclares every Collection method with the signatures of the
 * installed doctrine/collections major (PersistentCollectionCompatibility), and PHP requires ours to be compatible:
 * native types for doctrine/collections 3, no native return types for 2. The switch is ODM's own; class_exists()
 * comes first because defined() does not autoload, and it loads Criteria before ODM makes the same check.
 */
if (class_exists(Criteria::class) && !defined(Criteria::class . '::ASC')) {
    /**
     * Doctrine's Collection methods with the doctrine/collections 3 signatures.
     *
     * @template T
     */
    trait DoctrineCollectionBehaviour
    {
        /** @use DoctrineCollectionOperations<T> */
        use DoctrineCollectionOperations;

        public function add(mixed $element): void
        {
            $this->doctrineAdd($element);
        }

        /**
         * @return T|null null on an empty list, where ArrayCollection returns false
         */
        #[NoDiscard]
        public function first(): mixed
        {
            return $this->doctrineFirst();
        }

        /**
         * @return T|null null on an empty list, where ArrayCollection returns false
         */
        #[NoDiscard]
        public function last(): mixed
        {
            return $this->doctrineLast();
        }

        /**
         * @return int|null
         */
        #[NoDiscard]
        public function key(): int|string|null
        {
            return $this->doctrineKey();
        }

        /**
         * @return T|false false past the end, as in ArrayCollection
         */
        #[NoDiscard]
        public function current(): mixed
        {
            return $this->doctrineCurrent();
        }

        /**
         * @return T|false false past the end, as in ArrayCollection
         */
        public function next(): mixed
        {
            return $this->doctrineNext();
        }

        #[NoDiscard]
        public function contains(mixed $element): bool
        {
            return $this->doctrineContains($element);
        }

        #[NoDiscard]
        public function isEmpty(): bool
        {
            return $this->elements === [];
        }

        #[NoDiscard]
        public function containsKey(string|int $key): bool
        {
            return $this->doctrineContainsKey($key);
        }

        /**
         * @return T|null null for a missing or string key
         */
        #[NoDiscard]
        public function get(string|int $key): mixed
        {
            return $this->doctrineGet($key);
        }

        /**
         * @return list<int>
         */
        #[NoDiscard]
        public function getKeys(): array
        {
            return $this->doctrineKeys();
        }

        /**
         * @return list<T>
         */
        #[NoDiscard]
        public function getValues(): array
        {
            return $this->elements;
        }

        /**
         * @return list<T>
         */
        #[NoDiscard]
        public function toArray(): array
        {
            return $this->elements;
        }

        /**
         * @return list<T> reindexed, unlike ArrayCollection
         */
        #[NoDiscard]
        public function slice(int $offset, ?int $length = null): array
        {
            return $this->doctrineSlice($offset, $length);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return bool
         */
        #[NoDiscard]
        public function exists(Closure $p): bool
        {
            return $this->doctrineExists($p);
        }

        /**
         * @return int|false
         */
        #[NoDiscard]
        public function indexOf(mixed $element): int|string|false
        {
            return $this->doctrineIndexOf($element);
        }

        /**
         * @param Closure(T, int): bool $p
         *
         * @return static
         */
        #[NoDiscard]
        public function filter(Closure $p): Collection
        {
            return $this->doctrineFilter($p);
        }

        /**
         * @template U
         *
         * @param Closure(T): U $func
         *
         * @return ArrayCollection<int, U>
         */
        #[NoDiscard]
        public function map(Closure $func): Collection
        {
            return $this->doctrineMap($func);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return array{static, static}
         */
        #[NoDiscard]
        public function partition(Closure $p): array
        {
            return $this->doctrinePartition($p);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return bool
         */
        #[NoDiscard]
        public function forAll(Closure $p): bool
        {
            return $this->doctrineForAll($p);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return T|null
         */
        #[NoDiscard]
        public function findFirst(Closure $p): mixed
        {
            return $this->doctrineFindFirst($p);
        }

        /**
         * @template R
         *
         * @param Closure(R, T, int): R $func the index is passed when the reducer declares a third parameter
         * @param R $initial
         *
         * @return R
         */
        #[NoDiscard]
        public function reduce(Closure $func, mixed $initial = null): mixed
        {
            return $this->reduced($func, $initial);
        }

        /**
         * @return T|null the removed element; null for a missing or string key
         */
        public function remove(string|int $key): mixed
        {
            return $this->doctrineRemove($key);
        }

        public function removeElement(mixed $element): bool
        {
            return $this->doctrineRemoveElement($element);
        }

        public function set(string|int $key, mixed $value): void
        {
            $this->doctrineSet($key, $value);
        }

        /**
         * @return non-negative-int
         */
        #[NoDiscard]
        public function count(): int
        {
            return count($this->elements);
        }

        /**
         * @return ArrayIterator<int, T>
         */
        #[NoDiscard]
        public function getIterator(): Traversable
        {
            return $this->doctrineIterator();
        }

        #[NoDiscard]
        public function offsetExists(mixed $offset): bool
        {
            return $this->doctrineOffsetExists($offset);
        }

        /**
         * @return T|null
         */
        #[NoDiscard]
        public function offsetGet(mixed $offset): mixed
        {
            return $this->doctrineOffsetGet($offset);
        }

        public function offsetSet(mixed $offset, mixed $value): void
        {
            $this->doctrineOffsetSet($offset, $value);
        }

        public function offsetUnset(mixed $offset): void
        {
            $this->doctrineOffsetUnset($offset);
        }

        public function clear(): void
        {
            $this->elements = [];
        }

        /**
         * Criteria are applied as ArrayCollection applies them; the result is this list's class, reindexed.
         *
         * @return static
         */
        #[NoDiscard]
        public function matching(Criteria $criteria): ReadableCollection&Selectable
        {
            return $this->doctrineMatching($criteria);
        }
    }
} else {
    /**
     * Doctrine's Collection methods with the doctrine/collections 2 signatures: no native return types.
     *
     * @template T
     */
    trait DoctrineCollectionBehaviour
    {
        /** @use DoctrineCollectionOperations<T> */
        use DoctrineCollectionOperations;

        /**
         * @return void
         */
        public function add(mixed $element)
        {
            $this->doctrineAdd($element);
        }

        /**
         * @return T|null null on an empty list, where ArrayCollection returns false
         */
        #[NoDiscard]
        public function first()
        {
            return $this->doctrineFirst();
        }

        /**
         * @return T|null null on an empty list, where ArrayCollection returns false
         */
        #[NoDiscard]
        public function last()
        {
            return $this->doctrineLast();
        }

        /**
         * @return int|null
         */
        #[NoDiscard]
        public function key()
        {
            return $this->doctrineKey();
        }

        /**
         * @return T|false false past the end, as in ArrayCollection
         */
        #[NoDiscard]
        public function current()
        {
            return $this->doctrineCurrent();
        }

        /**
         * @return T|false false past the end, as in ArrayCollection
         */
        public function next()
        {
            return $this->doctrineNext();
        }

        /**
         * @return bool
         */
        #[NoDiscard]
        public function contains(mixed $element)
        {
            return $this->doctrineContains($element);
        }

        /**
         * @return bool
         */
        #[NoDiscard]
        public function isEmpty()
        {
            return $this->elements === [];
        }

        /**
         * @return bool
         */
        #[NoDiscard]
        public function containsKey(string|int $key)
        {
            return $this->doctrineContainsKey($key);
        }

        /**
         * @return T|null null for a missing or string key
         */
        #[NoDiscard]
        public function get(string|int $key)
        {
            return $this->doctrineGet($key);
        }

        /**
         * @return list<int>
         */
        #[NoDiscard]
        public function getKeys()
        {
            return $this->doctrineKeys();
        }

        /**
         * @return list<T>
         */
        #[NoDiscard]
        public function getValues()
        {
            return $this->elements;
        }

        /**
         * @return list<T>
         */
        #[NoDiscard]
        public function toArray()
        {
            return $this->elements;
        }

        /**
         * @return list<T> reindexed, unlike ArrayCollection
         */
        #[NoDiscard]
        public function slice(int $offset, ?int $length = null)
        {
            return $this->doctrineSlice($offset, $length);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return bool
         */
        #[NoDiscard]
        public function exists(Closure $p)
        {
            return $this->doctrineExists($p);
        }

        /**
         * @return int|false
         */
        #[NoDiscard]
        public function indexOf(mixed $element)
        {
            return $this->doctrineIndexOf($element);
        }

        /**
         * @param Closure(T, int): bool $p
         *
         * @return static
         */
        #[NoDiscard]
        public function filter(Closure $p)
        {
            return $this->doctrineFilter($p);
        }

        /**
         * @template U
         *
         * @param Closure(T): U $func
         *
         * @return ArrayCollection<int, U>
         */
        #[NoDiscard]
        public function map(Closure $func)
        {
            return $this->doctrineMap($func);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return array{static, static}
         */
        #[NoDiscard]
        public function partition(Closure $p)
        {
            return $this->doctrinePartition($p);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return bool
         */
        #[NoDiscard]
        public function forAll(Closure $p)
        {
            return $this->doctrineForAll($p);
        }

        /**
         * @param Closure(int, T): bool $p
         *
         * @return T|null
         */
        #[NoDiscard]
        public function findFirst(Closure $p)
        {
            return $this->doctrineFindFirst($p);
        }

        /**
         * @template R
         *
         * @param Closure(R, T, int): R $func the index is passed when the reducer declares a third parameter
         * @param R $initial
         *
         * @return R
         */
        #[NoDiscard]
        public function reduce(Closure $func, mixed $initial = null)
        {
            return $this->reduced($func, $initial);
        }

        /**
         * @return T|null the removed element; null for a missing or string key
         */
        public function remove(string|int $key)
        {
            return $this->doctrineRemove($key);
        }

        /**
         * @return bool
         */
        public function removeElement(mixed $element)
        {
            return $this->doctrineRemoveElement($element);
        }

        /**
         * @return void
         */
        public function set(string|int $key, mixed $value)
        {
            $this->doctrineSet($key, $value);
        }

        /**
         * @return non-negative-int
         */
        #[ReturnTypeWillChange]
        #[NoDiscard]
        public function count()
        {
            return count($this->elements);
        }

        /**
         * @return ArrayIterator<int, T>
         */
        #[ReturnTypeWillChange]
        #[NoDiscard]
        public function getIterator()
        {
            return $this->doctrineIterator();
        }

        /**
         * @return bool
         */
        #[ReturnTypeWillChange]
        #[NoDiscard]
        public function offsetExists(mixed $offset)
        {
            return $this->doctrineOffsetExists($offset);
        }

        /**
         * @return T|null
         */
        #[ReturnTypeWillChange]
        #[NoDiscard]
        public function offsetGet(mixed $offset)
        {
            return $this->doctrineOffsetGet($offset);
        }

        /**
         * @return void
         */
        #[ReturnTypeWillChange]
        public function offsetSet(mixed $offset, mixed $value)
        {
            $this->doctrineOffsetSet($offset, $value);
        }

        /**
         * @return void
         */
        #[ReturnTypeWillChange]
        public function offsetUnset(mixed $offset)
        {
            $this->doctrineOffsetUnset($offset);
        }

        /**
         * @return void
         */
        public function clear()
        {
            $this->elements = [];
        }

        /**
         * Criteria are applied as ArrayCollection applies them; the result is this list's class, reindexed.
         *
         * @return static
         */
        #[NoDiscard]
        public function matching(Criteria $criteria)
        {
            return $this->doctrineMatching($criteria);
        }
    }
}
