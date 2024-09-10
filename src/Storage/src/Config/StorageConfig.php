<?php

declare(strict_types=1);

namespace Spiral\Storage\Config;

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
use Spiral\Core\Exception\ConfigException;
use Spiral\Core\InjectableConfig;
use Spiral\Storage\Storage;
use Spiral\Storage\Visibility;

class StorageConfig extends InjectableConfig
{
    public const CONFIG = 'storage';

    private readonly string $default;

    /** @var array<string, FilesystemAdapter> */
    private array $adapters = [];

    /**
     * @var array<string, string>
     */
    private array $distributions = [];

    public function __construct(array $config = [])
    {
        $config = $this->normalize($config);
        parent::__construct($config);

        $this->default = $config['default'] ?? '';
        $this->bootStorages($config);
    }

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

    private function normalize(array $config): array
    {
        $defaults = [
            'default' => Storage::DEFAULT_STORAGE,
            'servers' => [],
            'buckets' => [],
        ];

        return \array_merge($defaults, $config);
    }

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

    private function createAdapter(string $serverName, array $bucket, array $server): FilesystemAdapter
    {
        return match ($server['adapter']) {
            'local' => $this->createLocalAdapter($serverName, $bucket, $server),
            's3' => $this->createS3Adapter($serverName, $bucket, $server, false),
            's3-async' => $this->createS3Adapter($serverName, $bucket, $server, true),
            default => $this->createCustomAdapter($serverName, $server),
        };
    }

    private function createS3Adapter(string $serverName, array $bucket, array $server, bool $async): FilesystemAdapter
    {
        if (!$async && !\class_exists(Credentials::class)) {
            throw new ConfigException(
                'Can not create AWS credentials while creating "' . $serverName . '" server. '
                . 'Perhaps you forgot to install the "league/flysystem-aws-s3-v3" package?'
            );
        }

        $name = $bucket['bucket'] ?? $server['bucket'];
        $visibility = $bucket['visibility'] ?? $server['visibility'] ?? Visibility::VISIBILITY_PUBLIC;

        if ($async) {
            if (!\class_exists(AsyncAwsS3Adapter::class)) {
                throw new InvalidArgumentException(
                    'Can not create async S3 client, please install "league/flysystem-async-aws-s3"'
                );
            }

            return new AsyncAwsS3Adapter(
                new S3AsyncClient($this->createAwsConfig($bucket, $server, true)),
                $name,
                $bucket['prefix'] ?? $server['prefix'] ?? '',
                new AsyncAwsS3PortableVisibilityConverter($visibility)
            );
        }

        if (!\class_exists(AwsS3V3Adapter::class)) {
            throw new InvalidArgumentException(
                'Can not create S3 client, please install "league/flysystem-aws-s3-v3"'
            );
        }

        return new AwsS3V3Adapter(
            new S3Client($this->createAwsConfig($bucket, $server)),
            $name,
            $bucket['prefix'] ?? $server['prefix'] ?? '',
            new AwsS3PortableVisibilityConverter($visibility)
        );
    }

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

    private function createAwsConfig(array $bucket, array $server, bool $async = false): array
    {
        $config = [
            'region' => $bucket['region'] ?? $server['region'] ?? null,
            'endpoint' => $server['endpoint'] ?? null,
        ] + ($server['options'] ?? []);

        if (!$async) {
            $config['version'] = $server['version'] ?? 'latest';
            $config['credentials'] = new Credentials(
                $server['key'] ?? null,
                $server['secret'] ?? null,
                $server['token'] ?? null,
                $server['expires'] ?? null
            );

            return $config;
        }

        $config['accessKeyId'] = $server['key'] ?? null;
        $config['accessKeySecret'] = $server['secret'] ?? null;
        $config['sessionToken'] = $server['token'] ?? null;

        return $config;
    }
}
