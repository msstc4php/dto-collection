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
    /**
     * Holds the values, not just their keys: an object's key is its spl_object_id(), which PHP reuses once the
     * object is gone.
     *
     * @var array<string, mixed>
     */
    private array $hashed = [];

    /** @var list<mixed> */
    private array $unhashed = [];

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
            $this->hashed[$key] = $value;
        }
    }

    public function contains(mixed $value): bool
    {
        $key = $this->equality->hashKey($value);
        if ($key !== null) {
            return array_key_exists($key, $this->hashed);
        }

        foreach ($this->unhashed as $member) {
            if ($this->equality->equals($member, $value)) {
                return true;
            }
        }

        return false;
    }
}
