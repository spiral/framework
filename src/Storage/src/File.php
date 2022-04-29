<?php

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\File\ReadableTrait;
use Spiral\Storage\File\UriResolvableTrait;
use Spiral\Storage\File\WritableTrait;

final class File implements \Stringable, FileInterface
{
    use UriResolvableTrait;
    use ReadableTrait;
    use WritableTrait;

    public function __construct(
        private readonly BucketInterface $storage,
        private readonly string $pathname,
        private readonly ?UriResolverInterface $resolver = null
    ) {
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getId(): string
    {
        $name = $this->storage->getName();

        if ($name === null) {
            return $this->getPathname();
        }

        return \sprintf('%s://%s', $name, $this->getPathname());
    }

    public function getPathname(): string
    {
        return $this->pathname;
    }

    public function getBucket(): BucketInterface
    {
        return $this->storage;
    }

    protected function getResolver(): ?UriResolverInterface
    {
        return $this->resolver;
    }
}
