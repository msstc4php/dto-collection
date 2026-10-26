<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use Closure;
use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Internal\Callback;
use MSSTC4PHP\DtoCollection\ListOf;
use MSSTC4PHP\DtoCollection\TypedList;
use NoDiscard;

/**
 * Maps to another element type, and runs callbacks over the list.
 *
 * @template T
 */
trait MappingBehaviour
{
    /**
     * @template U
     *
     * @param ElementType<U>|string $type the results are checked against it
     * @param Closure(T, int): U $mapper
     *
     * @return ($type is string ? ListOf<mixed> : ListOf<U>)
     */
    #[NoDiscard]
    public function mapTo(ElementType|string $type, Closure $mapper): ListOf
    {
        return ListOf::ofType($type, $this->mapToArray($mapper));
    }

    /**
     * @template U
     *
     * @param ElementType<U>|string $type the results are checked against it
     * @param Closure(T, int): iterable<U> $mapper
     *
     * @return ($type is string ? ListOf<mixed> : ListOf<U>)
     */
    #[NoDiscard]
    public function flatMapTo(ElementType|string $type, Closure $mapper): ListOf
    {
        $flattened = [];
        foreach ($this->elements as $index => $element) {
            foreach (Callback::invoke($mapper, $element, $index) as $value) {
                $flattened[] = $value;
            }
        }

        return ListOf::ofType($type, $flattened);
    }

    /**
     * Unchecked: any value the mapper returns.
     *
     * @template U
     *
     * @param Closure(T, int): U $mapper
     *
     * @return list<U>
     */
    #[NoDiscard]
    public function mapToArray(Closure $mapper): array
    {
        $mapped = [];
        foreach ($this->elements as $index => $element) {
            $mapped[] = Callback::invoke($mapper, $element, $index);
        }

        return $mapped;
    }

    /**
     * @template C of TypedList<mixed>
     *
     * @param class-string<C> $class built with $class::fromArray(), which checks the results
     * @param Closure(T, int): mixed $mapper
     *
     * @return C
     */
    #[NoDiscard]
    public function mapInto(string $class, Closure $mapper): TypedList
    {
        return $class::fromArray($this->mapToArray($mapper));
    }

    /**
     * Runs $callback on each element for its side effect; a callback returning exactly false stops the loop.
     *
     * @param Closure(T, int): mixed $callback
     *
     * @return static this list
     */
    public function each(Closure $callback): self
    {
        foreach ($this->elements as $index => $element) {
            if (Callback::invoke($callback, $element, $index) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Passes this list to the first step, and each step's result to the next; without steps, returns this list.
     *
     * The steps stay untyped: each takes the previous step's result, which PHPDoc cannot chain over a variadic.
     */
    #[NoDiscard]
    public function pipe(Closure ...$steps): mixed
    {
        $result = $this;
        foreach ($steps as $step) {
            $result = $step($result);
        }

        return $result;
    }
}
