<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection;

/**
 * A list class whose element type is fixed by the class itself, so it can be built from plain values.
 *
 * Implemented by generated and hand-written collections; this is what dto-generator relies on.
 *
 * @template T
 *
 * @extends ReadableList<T>
 */
interface TypedList extends ReadableList
{
    /**
     * @param array<T> $elements keys are discarded
     *
     * @throws Exception\ElementTypeMismatch
     */
    public static function fromArray(array $elements): static;

    /**
     * @param iterable<T> $elements keys are discarded
     *
     * @throws Exception\ElementTypeMismatch
     */
    public static function fromIterable(iterable $elements): static;

    public static function empty(): static;
}
