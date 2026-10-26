<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Internal;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Exception\InvalidElementType;

/**
 * @internal
 */
final class ElementTypeParser
{
    private const SCALARS = ['int', 'string', 'float', 'bool'];

    private const UNSUPPORTED = ['mixed', 'array', 'callable', 'iterable', 'object', 'self', 'static', 'parent', 'void', 'never', 'false', 'true', 'null', 'resource'];

    private const CLASS_NAME = '/^\\\\?[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(?:\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*$/';

    /**
     * @return ElementType<mixed>
     */
    public static function parse(string $declaration): ElementType
    {
        $source = trim($declaration);
        if ($source === '') {
            throw self::invalid($declaration, 'it is empty');
        }
        if (str_contains($source, '(') || (str_contains($source, '|') && str_contains($source, '&'))) {
            throw self::invalid($declaration, 'DNF types need PHP 8.2');
        }

        $nullable = false;
        if (str_starts_with($source, '?')) {
            $source = substr($source, 1);
            if (strpbrk($source, '?|&') !== false) {
                throw self::invalid($declaration, '"?" applies to a single type only');
            }
            $nullable = true;
        }

        $types = [];
        foreach (explode('|', $source) as $part) {
            $part = trim($part);
            if (strtolower($part) === 'null') {
                if ($nullable) {
                    throw self::invalid($declaration, 'null appears twice');
                }
                $nullable = true;

                continue;
            }
            $type = self::single($declaration, $part);
            $key = $type->describe();
            if (array_key_exists($key, $types)) {
                throw self::invalid($declaration, sprintf('%s appears twice', $key));
            }
            $types[$key] = $type;
        }
        if ($types === []) {
            throw self::invalid($declaration, 'null alone is not an element type');
        }

        $type = ElementType::union(...$types);

        return $nullable ? $type->nullable() : $type;
    }

    /**
     * @return ElementType<mixed>
     */
    private static function single(string $declaration, string $part): ElementType
    {
        if (!str_contains($part, '&')) {
            $scalar = strtolower($part);

            return match ($scalar) {
                'int' => ElementType::integer(),
                'string' => ElementType::string(),
                'float' => ElementType::float(),
                'bool' => ElementType::boolean(),
                default => ElementType::ofClass(self::className($declaration, $part)),
            };
        }

        $classes = array_map(
            static fn (string $name): string => self::className($declaration, trim($name)),
            explode('&', $part),
        );
        try {
            return ElementType::intersection(...$classes);
        } catch (InvalidElementType $exception) {
            throw self::invalid($declaration, $exception->getMessage());
        }
    }

    /**
     * @return class-string
     */
    private static function className(string $declaration, string $name): string
    {
        $lower = strtolower($name);
        if ($name === '') {
            throw self::invalid($declaration, 'a type is missing around a separator');
        }
        if (in_array($lower, self::SCALARS, true)) {
            throw self::invalid($declaration, sprintf('%s cannot be part of an intersection', $lower));
        }
        if (in_array($lower, self::UNSUPPORTED, true)) {
            throw self::invalid($declaration, sprintf('%s is not supported as an element type', $lower));
        }
        if (preg_match(self::CLASS_NAME, $name) !== 1) {
            throw self::invalid($declaration, sprintf('"%s" is not a class name', $name));
        }
        if (!class_exists($name) && !interface_exists($name)) {
            throw self::invalid($declaration, sprintf('class or interface "%s" does not exist', ltrim($name, '\\')));
        }

        return $name;
    }

    private static function invalid(string $declaration, string $reason): InvalidElementType
    {
        return new InvalidElementType(sprintf('Invalid element type "%s": %s', $declaration, $reason));
    }
}
