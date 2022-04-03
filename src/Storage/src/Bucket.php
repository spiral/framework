<?php

declare(strict_types=1);

namespace Spiral\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\Bucket\ReadableTrait;
use Spiral\Storage\Bucket\UriResolvableInterface;
use Spiral\Storage\Bucket\WritableTrait;

class Bucket implements BucketInterface
{
    use ReadableTrait;
    use WritableTrait;

    public function __construct(
        protected FilesystemOperator $fs,
        protected ?string $name = null,
        protected ?UriResolverInterface $resolver = null
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function withName(?string $name): BucketInterface
    {
        $self = clone $this;
        $self->name = $name;

        return $self;
    }

    public function getUriResolver(): ?UriResolverInterface
    {
        return $this->resolver;
    }

    public function withUriResolver(?UriResolverInterface $resolver): UriResolvableInterface
    {
        $self = clone $this;
        $self->resolver = $resolver;

        return $self;
    }

    public function file(string $pathname): FileInterface
    {
        return new File($this, $pathname, $this->resolver);
    }

    public static function fromAdapter(
        FilesystemAdapter $adapter,
        string $name = null,
        UriResolverInterface $resolver = null
    ): self {
        $fs = new Filesystem($adapter);

        return new self($fs, $name, $resolver);
    }

    protected function getOperator(): FilesystemOperator
    {
        return $this->fs;
    }
}
