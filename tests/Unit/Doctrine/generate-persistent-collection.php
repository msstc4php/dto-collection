<?php

declare(strict_types=1);

// Runs in its own process: an incompatible signature is a fatal error, which would end the test run.

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\PersistentCollection\DefaultPersistentCollectionGenerator;

require __DIR__ . '/../../../vendor/autoload.php';

$generator = new DefaultPersistentCollectionGenerator(sys_get_temp_dir(), 'DtoCollectionProxies');
foreach (array_slice($argv, 1) as $class) {
    echo $generator->loadClass($class, Configuration::AUTOGENERATE_EVAL), "\n";
}
