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
     * @return iterable<string, array{string}>
     */
    public static function invalidDeclarations(): iterable
    {
        foreach (['', '   ', 'null', 'NULL', 'mixed', 'array', 'callable', 'iterable', 'object', 'self', 'static', 'parent', 'void',
            'never', 'false', 'true', 'resource', '?int|string', '?A&B', '??int', '?null', '(A&B)|null', 'A&B|C', 'int|int', 'int|INT', 'null|null',
            'No\Such', 'int|', '|int', 'Countable&', 'int&string', 'Countable&int', 'Countable&Countable', '?', 'int|?string', 'not a type'] as $declaration) {
            yield var_export($declaration, true) => [$declaration];
        }
    }

    #[DataProvider('invalidDeclarations')]
    public function testRejects(string $declaration): void
    {
        $this->expectException(InvalidElementType::class);

        ElementTypeParser::parse($declaration);
    }

    public function testRejectionNamesTheDeclaration(): void
    {
        $this->expectExceptionMessage('"int|mixed"');

        ElementTypeParser::parse('int|mixed');
    }
}
