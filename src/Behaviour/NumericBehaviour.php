<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Behaviour;

use MSSTC4PHP\DtoCollection\Exception\UnsupportedOperation;
use NoDiscard;

/**
 * Totals for lists of int, float or int|float. Other element types throw UnsupportedOperation.
 */
trait NumericBehaviour
{
    #[NoDiscard]
    public function sum(): int|float
    {
        return array_sum($this->numbers('sum'));
    }

    /**
     * @return float|null null on an empty list
     */
    #[NoDiscard]
    public function average(): ?float
    {
        $numbers = $this->numbers('average');

        return $numbers === [] ? null : array_sum($numbers) / count($numbers);
    }

    #[NoDiscard]
    public function min(): int|float|null
    {
        $numbers = $this->numbers('min');

        return $numbers === [] ? null : min($numbers);
    }

    #[NoDiscard]
    public function max(): int|float|null
    {
        $numbers = $this->numbers('max');

        return $numbers === [] ? null : max($numbers);
    }

    /**
     * @return list<int|float>
     */
    private function numbers(string $operation): array
    {
        if (!$this->elementType()->isNumeric()) {
            throw new UnsupportedOperation(sprintf('%s() needs a numeric element type; the list holds %s', $operation, $this->elementType()->describe()));
        }

        return array_values(array_filter($this->elements, static fn (mixed $element): bool => is_int($element) || is_float($element)));
    }
}
