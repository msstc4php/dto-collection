<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveDuplicatedReturnSelfDocblockRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withAutoloadPaths([__DIR__ . '/vendor/autoload.php'])
    ->withoutParallel()
    // Line 1.x runs on 8.1: Rector must never upgrade sources past it.
    ->withPhpVersion(PhpVersion::PHP_81)
    ->withPhpSets(php81: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withImportNames(removeUnusedImports: true)
    ->withSkip([
        NewlineAfterStatementRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        // `@return static` over a native `self` is how queries keep the concrete class for PHPStan; native static breaks ODM proxies.
        RemoveDuplicatedReturnSelfDocblockRector::class,
    ])
;
