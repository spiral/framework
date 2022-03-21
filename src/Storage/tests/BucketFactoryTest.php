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

        $this->assertSame($name, $bucket->getName());
        $this->assertNull($bucket->getUriResolver());
    }

    public function testCreatesBucketFromAdapterWithResolver(): void
    {
        $factory = new BucketFactory();

        $bucket = $factory->createFromAdapter(
            $this->createMock(FilesystemAdapter::class),
            $name = 'foo',
            $resolver = $this->createMock(UriResolverInterface::class)
        );

        $this->assertSame($name, $bucket->getName());
        $this->assertSame($resolver, $bucket->getUriResolver());
    }
}
