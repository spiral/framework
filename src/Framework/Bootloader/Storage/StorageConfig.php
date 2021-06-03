<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Storage;

use AsyncAws\S3\S3Client as S3AsyncClient;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\AsyncAwsS3\PortableVisibilityConverter as AsyncAwsS3PortableVisibilityConverter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter as AwsS3PortableVisibilityConverter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Spiral\Config\Exception\InvalidArgumentException;
use Spiral\Storage\Storage;
use Spiral\Storage\Visibility;

class StorageConfig
{
    /**
     * @var string
     */
    public const CONFIG = 'storage';

    /**
     * @var string
     */
    private $default;

    /**
     * @var array<string, FilesystemAdapter>
     */
    private $adapters = [];

    /**
     * @var array<string, string>
     */
    private $distributions = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = $this->normalize($config);

        $this->default = $config['default'];
        $this->bootStorages($config);
    }

    /**
     * @return string
     */
    public function getDefaultBucket(): string
    {
        return $this->default;
    }

    /**
     * @return array<string, FilesystemAdapter>
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * @return array<string, string>
     */
    public function getDistributions(): array
    {
        return $this->distributions;
    }

    /**
     * @param array $config
     * @return array
     */
    private function normalize(array $config): array
    {
        $defaults = [
            'default' => Storage::DEFAULT_STORAGE,
            'servers' => [],
            'buckets' => [],
        ];

        return \array_merge($defaults, $config);
    }

    /**
     * @param array $config
     */
    private function bootStorages(array $config): void
    {
        foreach ($config['buckets'] as $name => $bucket) {
            if (!\is_string($name)) {
                throw new InvalidArgumentException(
                    \vsprintf('Storage bucket config key must be a string, but %s defined', [
                        \get_debug_type($name),
                    ])
                );
            }

            $serverName = $bucket['server'] ?? null;
            if (!\is_string($serverName)) {
                throw new InvalidArgumentException(
                    \vsprintf('Storage bucket `%s.server` config key required and must be a string, but %s defined', [
                        $name,
                        \get_debug_type($serverName),
                    ])
                );
            }

            $server = $config['servers'][$serverName] ?? null;
            if (!\is_array($server)) {
                throw new InvalidArgumentException(
                    \vsprintf('Storage bucket `%s` relates to non-existing server `%s`', [
                        $name,
                        $serverName,
                    ])
                );
            }

            $adapter = $server['adapter'] ?? null;
            if (!\is_string($adapter)) {
                throw new InvalidArgumentException(
                    \vsprintf('Storage server `%s.adapter` config key required and must be a string, but %s defined', [
                        $serverName,
                        \get_debug_type($adapter),
                    ])
                );
            }

            $adapter = $this->createAdapter($serverName, $bucket, $server);

            $this->adapters[$name] = $adapter;

            if (isset($bucket['distribution'])) {
                $this->distributions[$name] = $bucket['distribution'];
            }
        }
    }

    /**
     * @param string $serverName
     * @param array $bucket
     * @param array $server
     * @return FilesystemAdapter
     */
    private function createAdapter(string $serverName, array $bucket, array $server): FilesystemAdapter
    {
        switch ($server['adapter']) {
            case 'local':
                return $this->createLocalAdapter($serverName, $bucket, $server);

            case 's3':
                return $this->createS3Adapter($bucket, $server, false);

            case 's3-async':
                return $this->createS3Adapter($bucket, $server, true);

            default:
                return $this->createCustomAdapter($serverName, $server);
        }
    }

    /**
     * @param array $bucket
     * @param array $server
     * @param bool $async
     * @return FilesystemAdapter
     */
    private function createS3Adapter(array $bucket, array $server, bool $async): FilesystemAdapter
    {
        $config = [
            'version'     => $server['version'] ?? 'latest',
            'region'      => $bucket['region'] ?? $server['region'] ?? null,
            'endpoint'    => $server['endpoint'] ?? null,
            'credentials' => new Credentials(
                $server['key'] ?? null,
                $server['secret'] ?? null,
                $server['token'] ?? null,
                $server['expires'] ?? null
            ),
        ];

        $name = $bucket['bucket'] ?? $server['bucket'];
        $visibility = $bucket['visibility'] ?? $server['bucket'] ?? Visibility::VISIBILITY_PUBLIC;

        if ($async) {
            if (!\class_exists(AsyncAwsS3Adapter::class)) {
                throw new InvalidArgumentException(
                    'Can not create async S3 client, please install "league/flysystem-async-aws-s3"'
                );
            }

            return new AsyncAwsS3Adapter(
                new S3AsyncClient($config),
                $name,
                $bucket['prefix'] ?? $server['prefix'] ?? '',
                new AsyncAwsS3PortableVisibilityConverter(
                    $visibility
                )
            );
        }

        if (!\class_exists(AwsS3V3Adapter::class)) {
            throw new InvalidArgumentException(
                'Can not create S3 client, please install "league/flysystem-aws-s3-v3"'
            );
        }

        return new AwsS3V3Adapter(
            new S3Client($config),
            $name,
            $bucket['prefix'] ?? $server['prefix'] ?? '',
            new AwsS3PortableVisibilityConverter(
                $visibility
            )
        );
    }

    /**
     * @param string $serverName
     * @param array $bucket
     * @param array $server
     * @return FilesystemAdapter
     */
    private function createLocalAdapter(string $serverName, array $bucket, array $server): FilesystemAdapter
    {
        if (!\is_string($server['directory'] ?? null)) {
            throw new InvalidArgumentException(
                \vsprintf('Storage server `%s.directory` config key required and must be a string, but %s defined', [
                    $serverName,
                    \get_debug_type($server['directory'] ?? null),
                ])
            );
        }

        $visibility = new PortableVisibilityConverter(
            $server['visibility']['public']['file'] ?? 0644,
            $server['visibility']['private']['file'] ?? 0600,
            $server['visibility']['public']['dir'] ?? 0755,
            $server['visibility']['private']['dir'] ?? 0700,
            $bucket['visibility'] ?? $server['visibility']['default'] ?? Visibility::VISIBILITY_PRIVATE
        );

        $directory = \implode('/', [
            \rtrim($server['directory'], '/'),
            \trim($bucket['prefix'] ?? '', '/'),
        ]);

        return new LocalFilesystemAdapter(\rtrim($directory, '/'), $visibility);
    }

    /**
     * @param string $serverName
     * @param array $server
     * @return FilesystemAdapter
     */
    private function createCustomAdapter(string $serverName, array $server): FilesystemAdapter
    {
        $adapter = $server['adapter'];
        $isFilesystemAdapter = \is_subclass_of($adapter, FilesystemAdapter::class, true);

        if (!$isFilesystemAdapter) {
            throw new InvalidArgumentException(
                \vsprintf('Storage server `%s` must be a class string of %s, but `%s` defined', [
                    $serverName,
                    FilesystemAdapter::class,
                    $adapter,
                ])
            );
        }

        try {
            return new $adapter(...\array_values($server['options'] ?? []));
        } catch (\Throwable $e) {
            $message = 'An error occurred while server `%s` initializing: %s';
            throw new InvalidArgumentException(\sprintf($message, $serverName, $e->getMessage()), 0, $e);
        }
    }
}
