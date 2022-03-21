<?php

declare(strict_types=1);

namespace Spiral\Storage;

use League\Flysystem\FilesystemAdapter;
use Spiral\Distribution\UriResolverInterface;

interface BucketFactoryInterface
{
    /**
     * Create a bucket from given adapter.
     */
    public function createFromAdapter(
        FilesystemAdapter $adapter,
        string $name = null,
        UriResolverInterface $resolver = null
    ): BucketInterface;
}
