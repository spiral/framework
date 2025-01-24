<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Config;

use AsyncAws\S3\S3Client as S3AsyncClient;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter as AwsS3PortableVisibilityConverter;
use League\Flysystem\AsyncAwsS3\PortableVisibilityConverter as AsyncAwsS3PortableVisibilityConverter;
use PHPUnit\Framework\TestCase;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\Visibility;

final class StorageConfigTest extends TestCase
{
    public function testGetDefaultBucket(): void
    {
        $config = new StorageConfig([
            'default' => 'foo',
        ]);

        self::assertSame('foo', $config->getDefaultBucket());
    }

    public function testS3Adapter(): void
    {
        $config = new StorageConfig($this->getConfig());

        self::assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null,
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AsyncAdapter(): void
    {
        $config = new StorageConfig($this->getConfig());

        self::assertEquals(new AsyncAwsS3Adapter(
            new S3AsyncClient([
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'accessKeyId' => 'test-key',
                'accessKeySecret' => 'test-secret',
                'sessionToken' => null,
            ]),
            'test-bucket',
            'test-prefix',
            new AsyncAwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads-async']);
    }

    public function testS3AdapterWithOverriddenBucket(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'bucket' => 'overridden'],
        ]]));

        self::assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null,
                ),
                'use_path_style_endpoint' => true,
            ]),
            'overridden',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AsyncAdapterWithOverriddenBucket(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads-async' => ['server' => 's3-async', 'bucket' => 'overridden'],
        ]]));

        self::assertEquals(new AsyncAwsS3Adapter(
            new S3AsyncClient([
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'accessKeyId' => 'test-key',
                'accessKeySecret' => 'test-secret',
                'sessionToken' => null,
            ]),
            'overridden',
            'test-prefix',
            new AsyncAwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads-async']);
    }

    public function testS3AdapterWithOverriddenRegion(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'region' => 'overridden'],
        ]]));

        self::assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'overridden',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null,
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AsyncAdapterWithOverriddenRegion(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads-async' => ['server' => 's3-async', 'region' => 'overridden'],
        ]]));

        self::assertEquals(new AsyncAwsS3Adapter(
            new S3AsyncClient([
                'region' => 'overridden',
                'endpoint' => 'test-endpoint',
                'accessKeyId' => 'test-key',
                'accessKeySecret' => 'test-secret',
                'sessionToken' => null,
            ]),
            'test-bucket',
            'test-prefix',
            new AsyncAwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads-async']);
    }

    public function testS3AdapterWithOverriddenVisibility(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'visibility' => Visibility::VISIBILITY_PRIVATE],
        ]]));

        self::assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null,
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PRIVATE),
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AsyncAdapterWithOverriddenVisibility(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads-async' => ['server' => 's3-async', 'visibility' => Visibility::VISIBILITY_PRIVATE],
        ]]));

        self::assertEquals(new AsyncAwsS3Adapter(
            new S3AsyncClient([
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'accessKeyId' => 'test-key',
                'accessKeySecret' => 'test-secret',
                'sessionToken' => null,
            ]),
            'test-bucket',
            'test-prefix',
            new AsyncAwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PRIVATE),
        ), $config->getAdapters()['uploads-async']);
    }

    public function testS3AdapterWithOverriddenPrefix(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'prefix' => 'overridden'],
        ]]));

        self::assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null,
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'overridden',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AsyncAdapterWithOverriddenPrefix(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads-async' => ['server' => 's3-async', 'prefix' => 'overridden'],
        ]]));

        self::assertEquals(new AsyncAwsS3Adapter(
            new S3AsyncClient([
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'accessKeyId' => 'test-key',
                'accessKeySecret' => 'test-secret',
                'sessionToken' => null,
            ]),
            'test-bucket',
            'overridden',
            new AsyncAwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC),
        ), $config->getAdapters()['uploads-async']);
    }

    private function getConfig(array $config = []): array
    {
        return $config + [
            'servers' => [
                's3' => [
                    'version' => 'latest',
                    'adapter' => 's3',
                    'endpoint' => 'test-endpoint',
                    'region' => 'test-region',
                    'bucket' => 'test-bucket',
                    'key' => 'test-key',
                    'secret' => 'test-secret',
                    'prefix' => 'test-prefix',
                    'options' => ['use_path_style_endpoint' => true],
                ],
                's3-async' => [
                    'adapter' => 's3-async',
                    'endpoint' => 'test-endpoint',
                    'region' => 'test-region',
                    'bucket' => 'test-bucket',
                    'key' => 'test-key',
                    'secret' => 'test-secret',
                    'prefix' => 'test-prefix',
                ],
            ],
            'buckets' => [
                'uploads' => [
                    'server' => 's3',
                ],
                'uploads-async' => [
                    'server' => 's3-async',
                ],
            ],
        ];
    }
}
