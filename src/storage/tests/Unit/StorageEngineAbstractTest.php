<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit;

use League\Flysystem\FilesystemOperator;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Storage;
use Spiral\Tests\Storage\Traits\LocalFsBuilderTrait;

abstract class StorageEngineAbstractTest extends UnitTestCase
{
    use LocalFsBuilderTrait;

    /**
     * @param string|null $fs
     * @param FilesystemOperator|null $fileSystem
     *
     * @return Storage
     *
     * @throws StorageException
     * @throws ConfigException
     * @throws \ReflectionException
     */
    protected function buildSimpleStorageEngine(
        ?string $fs = null,
        ?FilesystemOperator $fileSystem = null
    ): Storage {
        $engine = new Storage($this->buildStorageConfig(), $this->getUriParser());

        if (!empty($fs) && $fileSystem !== null) {
            $this->mountStorageEngineFileSystem($engine, $fs, $fileSystem);
        }

        return $engine;
    }

    /**
     * @param Storage $engine
     * @param string $fs
     * @param FilesystemOperator $fileSystem
     *
     * @throws \ReflectionException
     */
    protected function mountStorageEngineFileSystem(
        Storage $engine,
        string $fs,
        FilesystemOperator $fileSystem
    ): void {
        $this->callNotPublicMethod($engine, 'mountFilesystem', [$fs, $fileSystem]);
    }
}
