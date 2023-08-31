<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Config;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter as AwsS3PortableVisibilityConverter;
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

        $this->assertSame('foo', $config->getDefaultBucket());
    }

    public function testS3Adapter(): void
    {
        $config = new StorageConfig($this->getConfig());

        $this->assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC)
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AdapterWithOverriddenBucket(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'bucket' => 'overridden']
        ]]));

        $this->assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null
                ),
                'use_path_style_endpoint' => true,
            ]),
            'overridden',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC)
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AdapterWithOverriddenRegion(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'region' => 'overridden']
        ]]));

        $this->assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'overridden',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC)
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AdapterWithOverriddenVisibility(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'visibility' => Visibility::VISIBILITY_PRIVATE]
        ]]));

        $this->assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'test-prefix',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PRIVATE)
        ), $config->getAdapters()['uploads']);
    }

    public function testS3AdapterWithOverriddenPrefix(): void
    {
        $config = new StorageConfig($this->getConfig(['buckets' => [
            'uploads' => ['server' => 's3', 'prefix' => 'overridden']
        ]]));

        $this->assertEquals(new AwsS3V3Adapter(
            new S3Client([
                'version' => 'latest',
                'region' => 'test-region',
                'endpoint' => 'test-endpoint',
                'credentials' => new Credentials(
                    'test-key',
                    'test-secret',
                    null,
                    null
                ),
                'use_path_style_endpoint' => true,
            ]),
            'test-bucket',
            'overridden',
            new AwsS3PortableVisibilityConverter(Visibility::VISIBILITY_PUBLIC)
        ), $config->getAdapters()['uploads']);
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
            ],
            'buckets' => [
                'uploads' => [
                    'server' => 's3',
                ]
            ]
        ];
    }
}
