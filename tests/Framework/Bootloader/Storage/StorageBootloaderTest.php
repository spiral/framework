<?php

declare(strict_types=1);

namespace Framework\Bootloader\Storage;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Mockery as m;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\Bucket;
use Spiral\Storage\BucketFactory;
use Spiral\Storage\BucketFactoryInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class StorageBootloaderTest extends BaseTestCase
{
    public const ENV = ['STORAGE_DEFAULT' => 'foo'];

    public function testBucketFactoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(BucketFactoryInterface::class, BucketFactory::class);
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(StorageConfig::CONFIG, [
            'default' => 'foo',
            'servers' => [],
            'buckets' => [],
        ]);
    }

    public function testConfigShouldBeInjectable(): void
    {
        self::assertTrue($this->getContainer()->hasInjector(StorageConfig::class));
    }

    public function testStorageInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(StorageInterface::class, StorageInterface::class);
    }

    public function testStorageBinding(): void
    {
        $this->assertContainerBoundAsSingleton(Storage::class, StorageInterface::class);
    }

    public function testBucketInterfaceBinding(): void
    {
        $storage = $this->mockContainer(StorageInterface::class);

        $storage->shouldReceive('bucket')->once()->andReturn(m::mock(BucketInterface::class));

        $this->assertContainerBoundAsSingleton(BucketInterface::class, BucketInterface::class);
        $this->assertContainerBoundAsSingleton(Bucket::class, BucketInterface::class);
    }

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
                    ],
                ],
            ]),
        );

        $this->getContainer()->bind(
            BucketFactoryInterface::class,
            $bucket = m::mock(BucketFactoryInterface::class),
        );

        $bucket->shouldReceive('createFromAdapter')->withArgs(fn(
            FilesystemAdapter $adapter,
            string $name,
            ?UriResolverInterface $resolver = null,
        ): bool => $adapter instanceof LocalFilesystemAdapter
            && $name === 'default'
            && $resolver === null)->once()->andReturn(
                $bucket = m::mock(BucketInterface::class),
            );

        $storage = $this->getContainer()->get(StorageInterface::class);

        self::assertSame($bucket, $storage->bucket('default'));
    }
}
