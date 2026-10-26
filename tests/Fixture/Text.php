<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Fixture;

/**
 * Helpers used as first-class callables, whose optional second parameter does not take an index.
 */
final class Text
{
    public static function normalised(string $text, bool $lowerCase = false): string
    {
        $trimmed = trim($text);

        return $lowerCase ? strtolower($trimmed) : $trimmed;
    }

    public static function joined(string ...$parts): string
    {
        return implode('', $parts);
    }
}
