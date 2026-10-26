<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit;

use MSSTC4PHP\DtoCollection\ElementType;
use MSSTC4PHP\DtoCollection\Exception\InvalidElementType;
use MSSTC4PHP\DtoCollection\Internal\ElementTypeParser;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Cat;
use MSSTC4PHP\DtoCollection\Tests\Fixture\Dog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ElementTypeParserTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validDeclarations(): iterable
    {
        yield 'int' => ['int', 'int'];
        yield 'case-insensitive scalars' => ['INT|String|Float|BOOL', 'int|string|float|bool'];
        yield 'spaces around separators' => [' int | null ', 'int|null'];
        yield 'leading question mark' => ['?int', 'int|null'];
        yield 'question mark inside spaces' => [' ?int ', 'int|null'];
        yield 'space after the question mark' => ['? int', 'int|null'];
        yield 'upper-case null' => ['int|NULL', 'int|null'];
        yield 'question mark on a class' => ['?' . Cat::class, Cat::class . '|null'];
        yield 'null first' => ['null|int', 'int|null'];
        yield 'intersection' => ['\Countable&\Traversable', 'Countable&Traversable'];
        yield 'intersection with spaces' => ['Countable & Traversable', 'Countable&Traversable'];
        yield 'union of classes' => ['\\' . Cat::class . '|' . Dog::class . '|null', Cat::class . '|' . Dog::class . '|null'];
    }

    #[DataProvider('validDeclarations')]
    public function testParses(string $declaration, string $described): void
    {
        self::assertSame($described, ElementTypeParser::parse($declaration)->describe());
        self::assertSame($described, ElementType::parse($declaration)->describe());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidDeclarations(): iterable
    {
        $cases = [
            '' => 'it is empty',
            '   ' => 'it is empty',
            'null' => 'null alone is not an element type',
            'NULL' => 'null alone is not an element type',
            'mixed' => 'mixed is not supported as an element type',
            'MIXED' => 'mixed is not supported as an element type',
            'array' => 'array is not supported as an element type',
            'callable' => 'callable is not supported as an element type',
            'iterable' => 'iterable is not supported as an element type',
            'object' => 'object is not supported as an element type',
            'self' => 'self is not supported as an element type',
            'static' => 'static is not supported as an element type',
            'parent' => 'parent is not supported as an element type',
            'void' => 'void is not supported as an element type',
            'never' => 'never is not supported as an element type',
            'false' => 'false is not supported as an element type',
            'true' => 'true is not supported as an element type',
            'resource' => 'resource is not supported as an element type',
            '?int|string' => '"?" applies to a single type only',
            '?A&B' => '"?" applies to a single type only',
            '??int' => '"?" applies to a single type only',
            '?null' => 'null appears twice',
            '?' => 'a type is missing around a separator',
            '(A&B)|null' => 'DNF types need PHP 8.2',
            '(Countable)' => 'DNF types need PHP 8.2',
            'A&B|C' => 'DNF types need PHP 8.2',
            'int|int' => 'int appears twice',
            'int|INT' => 'int appears twice',
            'null|null' => 'null appears twice',
            'int|null|NULL' => 'null appears twice',
            'No\\Such' => 'class or interface "No\\Such" does not exist',
            '\\No\\Such' => 'class or interface "No\\Such" does not exist',
            'int|' => 'a type is missing around a separator',
            '|int' => 'a type is missing around a separator',
            'Countable&' => 'a type is missing around a separator',
            'int&string' => 'int cannot be part of an intersection',
            'Countable&INT' => 'int cannot be part of an intersection',
            'Countable&Countable' => 'An intersection repeats a class: Countable&Countable',
            'int|?string' => '"?string" is not a class name',
            'not a type' => '"not a type" is not a class name',
        ];
        foreach ($cases as $declaration => $reason) {
            yield var_export($declaration, true) => [$declaration, $reason];
        }
    }

    #[DataProvider('invalidDeclarations')]
    public function testRejects(string $declaration, string $reason): void
    {
        $this->expectException(InvalidElementType::class);
        $this->expectExceptionMessage(sprintf('Invalid element type "%s": %s', $declaration, $reason));

        ElementTypeParser::parse($declaration);
    }

    public function testRejectionNamesTheDeclaration(): void
    {
        $this->expectExceptionMessage('"int|mixed"');

        ElementTypeParser::parse('int|mixed');
    }
}
