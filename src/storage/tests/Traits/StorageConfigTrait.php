<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Traits;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Storage\Config\DTO\FileSystemInfo\LocalInfo;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\Exception\ConfigException;

trait StorageConfigTrait
{
    /**
     * Build storage config by provided servers and buckets
     * If no buckets were defined it will be built for each defined server
     *
     * @param array|null $servers
     * @param array|null $buckets
     *
     * @return StorageConfig
     *
     * @throws ConfigException
     */
    protected function buildStorageConfig(?array $servers = null, ?array $buckets = null): StorageConfig
    {
        if (empty($servers)) {
            $servers[self::SERVER_NAME] = [
                LocalInfo::ADAPTER_KEY => LocalFilesystemAdapter::class,
                LocalInfo::OPTIONS_KEY => [
                    LocalInfo::ROOT_DIR_KEY => self::ROOT_DIR,
                    LocalInfo::HOST_KEY => self::CONFIG_HOST,
                ],
            ];
        }

        if (!empty($servers) && empty($buckets)) {
            $buckets = [];
            foreach ($servers as $server => $serverInfo) {
                $buckets[$this->buildBucketNameByServer($server)] = $this->buildServerBucketInfoDesc($server);
            }
        }

        return new StorageConfig([
            'servers' => $servers,
            'buckets' => $buckets
        ]);
    }

    protected function buildServerBucketInfoDesc(string $serverName): array
    {
        return [
            'server' => $serverName,
            'directory' => 'tmp/',
        ];
    }

    protected function buildBucketNameByServer(string $server): string
    {
        return $server . 'Bucket';
    }
}
