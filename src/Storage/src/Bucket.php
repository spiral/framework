<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @var FilesystemOperator
     */
    protected $fs;

    /**
     * @var UriResolverInterface|null
     */
    protected $resolver;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @param string|null $name
     * @param UriResolverInterface|null $resolver
     */
    public function __construct(FilesystemOperator $fs, string $name = null, UriResolverInterface $resolver = null)
    {
        $this->fs = $fs;
        $this->resolver = $resolver;
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function withName(?string $name): BucketInterface
    {
        $self = clone $this;
        $self->name = $name;

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function getUriResolver(): ?UriResolverInterface
    {
        return $this->resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function withUriResolver(?UriResolverInterface $resolver): UriResolvableInterface
    {
        $self = clone $this;
        $self->resolver = $resolver;

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function file(string $pathname): FileInterface
    {
        return new File($this, $pathname, $this->resolver);
    }

    /**
     * @param string|null $name
     * @param UriResolverInterface|null $resolver
     */
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
