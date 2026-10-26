<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Tests\Integration\Odm;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use PHPUnit\Framework\TestCase;

abstract class OdmTestCase extends TestCase
{
    protected DocumentManager $documents;

    private string $database;

    protected function setUp(): void
    {
        $uri = getenv('DTO_COLLECTION_MONGODB_URI');
        if (!is_string($uri) || $uri === '') {
            self::markTestSkipped('Set DTO_COLLECTION_MONGODB_URI to run ODM integration tests');
        }

        $directory = dirname(__DIR__, 3) . '/var/odm';
        foreach (['proxies', 'hydrators', 'collections'] as $subdirectory) {
            if (!is_dir($directory . '/' . $subdirectory)) {
                mkdir($directory . '/' . $subdirectory, 0o777, true);
            }
        }
        $configuration = new Configuration();
        $configuration->setProxyDir($directory . '/proxies');
        $configuration->setProxyNamespace('DtoCollectionTestProxies');
        $configuration->setHydratorDir($directory . '/hydrators');
        $configuration->setHydratorNamespace('DtoCollectionTestHydrators');
        $configuration->setPersistentCollectionDir($directory . '/collections');
        $configuration->setPersistentCollectionNamespace('DtoCollectionTestCollections');
        $configuration->setAutoGenerateProxyClasses(Configuration::AUTOGENERATE_EVAL);
        $configuration->setAutoGenerateHydratorClasses(Configuration::AUTOGENERATE_EVAL);
        $configuration->setAutoGeneratePersistentCollectionClasses(Configuration::AUTOGENERATE_EVAL);
        $configuration->setMetadataDriverImpl(AttributeDriver::create([dirname(__DIR__, 2) . '/Fixture/Odm']));

        $this->database = 'dto_collection_test_' . getmypid();
        $configuration->setDefaultDB($this->database);

        $this->documents = DocumentManager::create(new Client($uri, [], ['typeMap' => DocumentManager::CLIENT_TYPEMAP]), $configuration);
    }

    protected function tearDown(): void
    {
        if (isset($this->documents)) {
            $this->documents->getClient()->dropDatabase($this->database);
        }
    }

    /**
     * Writes everything and forgets it, so the next find() hydrates from MongoDB.
     */
    protected function flushAndClear(): void
    {
        $this->documents->flush();
        $this->documents->clear();
    }

    /**
     * The field as MongoDB stores it, decoded to plain PHP arrays.
     */
    protected function rawField(string $collection, string $id, string $field): mixed
    {
        $document = $this->documents->getClient()->selectCollection($this->database, $collection)->findOne(
            ['_id' => new ObjectId($id)],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']],
        );
        self::assertIsArray($document);

        return $document[$field] ?? null;
    }
}
