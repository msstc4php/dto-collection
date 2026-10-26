<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Internal;

use MSSTC4PHP\DtoCollection\Equality\Equality;

/**
 * Membership under an Equality: O(1) for values with a hash key, a pairwise scan for the rest.
 *
 * @internal
 */
final class HashSet
{
    /** @var array<string, true> */
    private array $hashed = [];

    /** @var list<mixed> */
    private array $unhashed = [];

    /** @var list<mixed> */
    private array $all = [];

    /**
     * @param iterable<mixed> $values
     */
    public function __construct(private readonly Equality $equality, iterable $values)
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function add(mixed $value): void
    {
        $key = $this->equality->hashKey($value);
        if ($key === null) {
            $this->unhashed[] = $value;
        } else {
            $this->hashed[$key] = true;
        }
        $this->all[] = $value;
    }

    public function contains(mixed $value): bool
    {
        $key = $this->equality->hashKey($value);
        if ($key !== null && isset($this->hashed[$key])) {
            return true;
        }

        foreach ($key === null ? $this->all : $this->unhashed as $member) {
            if ($this->equality->equals($member, $value)) {
                return true;
            }
        }

        return false;
    }
}
