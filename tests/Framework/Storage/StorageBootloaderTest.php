<?php

declare(strict_types=1);

namespace Framework\Storage;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Mockery as m;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\BucketFactoryInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\StorageInterface;
use Spiral\Tests\Framework\BaseTest;

final class StorageBootloaderTest extends BaseTest
{
    public function testCreatesStorageWithBucket(): void
    {
        $this->getContainer()->bind(
            StorageConfig::class,
            new StorageConfig([
                'servers' => [
                    'local' => [
                        'adapter' => 'local',
                        'directory' => 'uploads',
                    ],
                ],
                'buckets' => [
                    'default' => [
                        'server' => 'local',
                    ]
                ],
            ])
        );

        $this->getContainer()->bind(
            BucketFactoryInterface::class,
            $bucket = m::mock(BucketFactoryInterface::class)
        );

        $bucket->shouldReceive('createFromAdapter')->withArgs(function (
            FilesystemAdapter $adapter,
            string $name,
            UriResolverInterface $resolver = null
        ) {
            return $adapter instanceof LocalFilesystemAdapter
                && $name === 'default'
                && $resolver === null;
        })->once()->andReturn($bucket = m::mock(BucketInterface::class));

        $storage = $this->getContainer()->get(StorageInterface::class);

        $this->assertSame($bucket, $storage->bucket('default'));
    }
}
