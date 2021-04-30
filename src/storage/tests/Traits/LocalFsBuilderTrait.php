<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Traits;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Storage\Builder\AdapterFactory;
use Spiral\Storage\Config\DTO\FileSystemInfo\LocalInfo;
use Spiral\Storage\Exception\StorageException;

trait LocalFsBuilderTrait
{
    /**
     * @param bool|null $useVcsPrefix
     *
     * @return Filesystem
     *
     * @throws StorageException
     */
    protected function buildLocalFs(?bool $useVcsPrefix = false): Filesystem
    {
        return new Filesystem(
            $this->buildLocalAdapter($useVcsPrefix)
        );
    }

    /**
     * @param bool|null $useVcsPrefix
     *
     * @return LocalFilesystemAdapter
     *
     * @throws StorageException
     */
    protected function buildLocalAdapter(?bool $useVcsPrefix = false): FilesystemAdapter
    {
        return AdapterFactory::build($this->buildLocalInfo(self::SERVER_NAME, $useVcsPrefix));
    }

    /**
     * @param string|null $name
     * @param bool|null $useVcsPrefix
     *
     * @return LocalInfo
     *
     * @throws StorageException
     */
    protected function buildLocalInfo(
        ?string $name = self::SERVER_NAME,
        ?bool $useVcsPrefix = false
    ): LocalInfo {
        return new LocalInfo($name, $this->buildLocalInfoDescription($useVcsPrefix));
    }

    protected function buildLocalInfoDescription(?bool $useVcsPrefix = false): array
    {
        $prefix = $useVcsPrefix ? self::VFS_PREFIX : '';

        return [
            LocalInfo::ADAPTER_KEY => LocalFilesystemAdapter::class,
            LocalInfo::OPTIONS_KEY => [
                LocalInfo::ROOT_DIR_KEY => $prefix . self::ROOT_DIR,
                LocalInfo::HOST_KEY => self::CONFIG_HOST,
            ],
        ];
    }
}
