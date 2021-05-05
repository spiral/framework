<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\BucketInfo;
use Spiral\Storage\Config\DTO\BucketInfoInterface;
use Spiral\Storage\Config\DTO\FileSystemInfo;
use Spiral\Storage\Exception\StorageException;

/**
 * @psalm-type ConfigServerSection = array {
 *      adapter:  class-string<FilesystemAdapter>,
 *      options?: array<string, mixed>
 * }
 *
 * @psalm-type ConfigBucketSection = array {
 *      server:   string,
 *      options?: array<string, mixed>
 * }
 *
 * @psalm-type ConfigArray = array {
 *      servers: array<string, ConfigServerSection>,
 *      buckets: array<string, ConfigBucketSection>,
 *      temp:    string
 * }
 *
 * @psalm-type ConfigInputArray = array {
 *      servers?: array<string, ConfigServerSection>|null,
 *      buckets?: array<string, ConfigBucketSection>|null,
 *      temp?:    string|null
 * }
 *
 * @see FilesystemAdapter
 */
final class StorageConfig implements ConfigInterface
{
    /**
     * @var string
     */
    public const CONFIG = 'storage';

    /**
     * @var string
     */
    private const SERVERS_KEY = 'servers';

    /**
     * @var string
     */
    private const STORAGES_KEY = 'buckets';

    /**
     * @var string
     */
    private const TMP_DIR_KEY = 'temp';

    /**
     * @var ConfigArray
     */
    protected $config = [
        self::SERVERS_KEY  => [],
        self::STORAGES_KEY => [],
        self::TMP_DIR_KEY  => '',
    ];

    /**
     * Internal list allows to keep once built filesystems info
     *
     * @var FileSystemInfoInterface[]
     */
    protected $fileSystemsInfoList = [];

    /**
     * Internal list allows to keep once built buckets info
     *
     * @var BucketInfoInterface[]
     */
    protected $bucketsInfoList = [];

    /**
     * @param ConfigInputArray $config
     * @throws ConfigException
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->normalize($config);
    }

    /**
     * @param ConfigInputArray $config
     * @return ConfigArray
     * @throws ConfigException
     */
    private function normalize(array $config): array
    {
        $config = $this->normalizeServers($config);
        $config = $this->normalizeBuckets($config);
        $config = $this->normalizeTemporaryDirectory($config);

        return $config;
    }

    /**
     * @param ConfigInputArray $config
     * @return ConfigArray
     * @throws ConfigException
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function normalizeServers(array $config): array
    {
        if (! isset($config[self::SERVERS_KEY])) {
            $config[self::SERVERS_KEY] = [];

            return $config;
        }

        $servers = $config[self::SERVERS_KEY];

        if (! \is_array($servers)) {
            $message = 'Storage servers list must be an array, but `%s` defined';
            throw new ConfigException(\sprintf($message, $this->valueToString($servers)), 0x01);
        }

        foreach ($servers as $name => $server) {
            if (! \is_string($name)) {
                $message = 'Storage server name must be a string, but %s defined';
                $message = \sprintf($message, $this->valueToString($name));

                throw new ConfigException($message, 0x02);
            }

            if (! \is_array($server)) {
                $message = 'Storage server `%s` config must be an array, but %s defined';
                $message = \sprintf($message, $name, $this->valueToString($server));

                throw new ConfigException($message, 0x03);
            }

            $adapter = $server['adapter'] ?? null;

            if (! \is_string($adapter) || ! \is_subclass_of($adapter, FilesystemAdapter::class, true)) {
                $message = 'Storage server `%s` adapter must be a class ' .
                    'string that implements %s interface, but %s defined'
                ;

                $message = \sprintf($message, $name, FilesystemAdapter::class, $this->valueToString($adapter));

                throw new ConfigException($message, 0x04);
            }

            if (isset($server['options']) && ! \is_array($server['options'])) {
                $message = 'Storage server `%s` options must be an array, but %s defined';
                $message = \sprintf($message, $name, $this->valueToString($server['options']));

                throw new ConfigException($message, 0x05);
            }
        }

        return $config;
    }

    /**
     * @param ConfigInputArray $config
     * @return ConfigArray
     * @throws ConfigException
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function normalizeBuckets(array $config): array
    {
        if (! isset($config[self::STORAGES_KEY])) {
            $config[self::STORAGES_KEY] = [];

            return $config;
        }

        $buckets = $config[self::STORAGES_KEY];

        if (! \is_array($buckets)) {
            $message = 'Storage buckets list must be an array, but `%s` defined';
            throw new ConfigException(\sprintf($message, $this->valueToString($buckets)), 0x06);
        }

        foreach ($buckets as $name => $bucket) {
            if (! \is_string($name)) {
                $message = 'Storage bucket name must be a string, but %s defined';
                $message = \sprintf($message, $this->valueToString($name));

                throw new ConfigException($message, 0x07);
            }

            if (! \is_array($bucket)) {
                $message = 'Storage bucket `%s` config must be an array, but %s defined';
                $message = \sprintf($message, $name, $this->valueToString($bucket));

                throw new ConfigException($message, 0x08);
            }

            $server = $bucket['server'] ?? null;

            if (! \is_string($server)) {
                $message = 'Storage server of bucket `%s` must be a string, but %s defined';
                $message = \sprintf($message, $name, $this->valueToString($server));

                throw new ConfigException($message, 0x09);
            }

            if (! isset($config[self::SERVERS_KEY][$server])) {
                $variants = \implode(', ', \array_keys($config[self::SERVERS_KEY] ?? []));
                $message = 'Storage server `%s` of bucket `%s` has not been defined, one of [%s] required';
                $message = \sprintf($message, $server, $name, $variants);

                throw new ConfigException($message, 0x0A);
            }

            if (isset($bucket['options']) && ! \is_array($bucket['options'])) {
                $message = 'Storage bucket `%s` options must be an array, but %s defined';
                $message = \sprintf($message, $name, $this->valueToString($bucket['options']));

                throw new ConfigException($message, 0x0B);
            }
        }

        return $config;
    }

    /**
     * @param ConfigInputArray $config
     * @return ConfigArray
     * @throws ConfigException
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function normalizeTemporaryDirectory(array $config): array
    {
        if (! isset($config[self::TMP_DIR_KEY]) ) {
            $config[self::TMP_DIR_KEY] = \sys_get_temp_dir();

            return $config;
        }

        $directory = $config[self::TMP_DIR_KEY];

        if (! \is_string($directory)) {
            $message = 'Storage temporary directory must be a string, but `%s` defined';
            throw new ConfigException(\sprintf($message, $this->valueToString($directory)), 0x0C);
        }

        if (! \is_dir($directory)) {
            $message = 'Storage temporary directory `%s` must be a valid directory';
            throw new ConfigException(\sprintf($message, $directory), 0x0D);
        }

        if (! \is_writable($directory)) {
            $message = 'Storage temporary directory `%s` must be writable';
            throw new ConfigException(\sprintf($message, $directory), 0x0E);
        }

        return $config;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function valueToString($value): string
    {
        $suffix = \is_scalar($value) ? "($value)" : (string)$value;

        return \get_debug_type($value) . $suffix;
    }

    /**
     * {@inheritDoc}
     */
    public function getServersKeys(): array
    {
        return \array_keys($this->config[self::SERVERS_KEY]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasServer(string $key): bool
    {
        return isset($this->config[self::SERVERS_KEY][$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBucketsKeys(): array
    {
        return \array_keys($this->config[self::STORAGES_KEY]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasBucket(string $key): bool
    {
        return isset($this->config[self::STORAGES_KEY][$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getTmpDir(): string
    {
        return $this->config[self::TMP_DIR_KEY];
    }

    /**
     * {@inheritDoc}
     */
    public function buildFileSystemInfo(string $fs, ?bool $force = false): FileSystemInfoInterface
    {
        if (!$this->hasBucket($fs)) {
            throw new ConfigException(
                \sprintf('Bucket `%s` was not found', $fs)
            );
        }

        if (!$force && array_key_exists($fs, $this->fileSystemsInfoList)) {
            return $this->fileSystemsInfoList[$fs];
        }

        $bucketInfo = $this->buildBucketInfo($fs);

        if (!$this->hasServer($bucketInfo->getServer())) {
            throw new ConfigException(
                \sprintf(
                    'Server `%s` info for filesystem `%s` was not detected',
                    $bucketInfo->getServer(),
                    $fs
                )
            );
        }

        $serverInfo = $this->config[self::SERVERS_KEY][$bucketInfo->getServer()];

        switch ($this->extractServerAdapter($serverInfo)) {
            case \League\Flysystem\Local\LocalFilesystemAdapter::class:
                $fsInfoDTO = new FileSystemInfo\LocalInfo($fs, $serverInfo);
                break;
            case \League\Flysystem\AwsS3V3\AwsS3V3Adapter::class:
            case \League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter::class:
                $serverInfo[FileSystemInfo\Aws\AwsS3Info::OPTIONS_KEY] = array_merge(
                    [
                        FileSystemInfo\Aws\AwsS3Info::BUCKET_KEY => $bucketInfo->getOption(
                            BucketInfoInterface::BUCKET_KEY
                        )
                    ],
                    $serverInfo[FileSystemInfo\Aws\AwsS3Info::OPTIONS_KEY]
                );

                $fsInfoDTO = new FileSystemInfo\Aws\AwsS3Info($fs, $serverInfo);
                break;
            default:
                throw new ConfigException(
                    \sprintf('Adapter can\'t be identified for filesystem `%s`', $fs)
                );
        }

        $this->fileSystemsInfoList[$fs] = $fsInfoDTO;
        $bucketInfo->setFileSystemInfo($fsInfoDTO);

        return $this->fileSystemsInfoList[$fs];
    }

    /**
     * {@inheritDoc}
     */
    public function buildBucketInfo(string $bucketLabel, ?bool $force = false): BucketInfoInterface
    {
        if (!$this->hasBucket($bucketLabel)) {
            throw new StorageException(
                \sprintf('Bucket `%s` was not found', $bucketLabel)
            );
        }

        if (!$force && array_key_exists($bucketLabel, $this->bucketsInfoList)) {
            return $this->bucketsInfoList[$bucketLabel];
        }

        $bucketInfo = $this->config[self::STORAGES_KEY][$bucketLabel];

        $this->bucketsInfoList[$bucketLabel] = new BucketInfo(
            $bucketLabel,
            $bucketInfo[BucketInfoInterface::SERVER_KEY],
            $bucketInfo
        );

        return $this->bucketsInfoList[$bucketLabel];
    }

    /**
     * Extract server adapter class from server description
     *
     * @param array $serverInfo
     *
     * @return string|null
     */
    private function extractServerAdapter(array $serverInfo): ?string
    {
        return $serverInfo[FileSystemInfoInterface::ADAPTER_KEY] ?? null;
    }
}
