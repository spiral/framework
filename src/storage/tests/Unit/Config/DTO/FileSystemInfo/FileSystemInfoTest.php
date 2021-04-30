<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Config\DTO\FileSystemInfo;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\FileSystemInfo\LocalInfo;
use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class FileSystemInfoTest extends UnitTestCase
{
    /**
     * @throws StorageException
     */
    public function testValidateNoOptionsFailed(): void
    {
        $fsName = 'some';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(\sprintf('Filesystem `%s` needs options defined', $fsName));

        new LocalInfo(
            $fsName,
            [LocalInfo::ADAPTER_KEY => LocalFilesystemAdapter::class]
        );
    }

    /**
     * @throws StorageException
     */
    public function testValidateNoAdapterClassFailed(): void
    {
        $fsName = 'some';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(\sprintf('Filesystem `%s` needs adapter class defined', $fsName));

        new LocalInfo(
            $fsName,
            [
                LocalInfo::OPTIONS_KEY => [
                    LocalInfo::ROOT_DIR_KEY => '/some/root/',
                    LocalInfo::HOST_KEY => self::CONFIG_HOST,
                ],
            ]
        );
    }
}
