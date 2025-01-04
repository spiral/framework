<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use League\Flysystem\FilesystemAdapter;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\BucketFactory;

final class BucketFactoryTest extends TestCase
{
    public function testCreatesBucketFromAdapter(): void
    {
        $factory = new BucketFactory();

        $bucket = $factory->createFromAdapter(
            $this->createMock(FilesystemAdapter::class),
            $name = 'foo',
        );

        self::assertSame($name, $bucket->getName());
        self::assertNull($bucket->getUriResolver());
    }

    public function testCreatesBucketFromAdapterWithResolver(): void
    {
        $factory = new BucketFactory();

        $bucket = $factory->createFromAdapter(
            $this->createMock(FilesystemAdapter::class),
            $name = 'foo',
            $resolver = $this->createMock(UriResolverInterface::class)
        );

        self::assertSame($name, $bucket->getName());
        self::assertSame($resolver, $bucket->getUriResolver());
    }
}
