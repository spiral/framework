<?php

declare(strict_types=1);

namespace Spiral\Storage;

use League\Flysystem\FilesystemAdapter;
use Spiral\Distribution\UriResolverInterface;

final class BucketFactory implements BucketFactoryInterface
{
    public function createFromAdapter(
        FilesystemAdapter $adapter,
        string $name = null,
        UriResolverInterface $resolver = null
    ): BucketInterface {
        return Bucket::fromAdapter($adapter, $name, $resolver);
    }
}
