<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Unit;

use Countable;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testAutoloads(): void
    {
        self::assertTrue(interface_exists(Countable::class));
    }
}
