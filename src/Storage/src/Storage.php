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
use Spiral\Distribution\ResolverInterface;
use Spiral\Storage\File\EntryInterface;
use Spiral\Storage\Storage\ReadableTrait;
use Spiral\Storage\Storage\UriResolvableInterface;
use Spiral\Storage\Storage\WritableTrait;

/**
 * @internal Storage is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Storage
 */
final class Storage implements StorageInterface
{
    use ReadableTrait;
    use WritableTrait;

    /**
     * @var FilesystemOperator
     */
    private $fs;

    /**
     * @var ResolverInterface|null
     */
    private $resolver;

    /**
     * @param FilesystemOperator $fs
     * @param ResolverInterface|null $resolver
     */
    public function __construct(FilesystemOperator $fs, ResolverInterface $resolver = null)
    {
        $this->fs = $fs;
        $this->resolver = $resolver;
    }

    /**
     * @param FilesystemAdapter $adapter
     * @param ResolverInterface|null $resolver
     * @return static
     */
    public static function fromAdapter(FilesystemAdapter $adapter, ResolverInterface $resolver = null): self
    {
        $fs = new Filesystem($adapter);

        return new self($fs, $resolver);
    }

    /**
     * @return FilesystemOperator
     */
    protected function getOperator(): FilesystemOperator
    {
        return $this->fs;
    }

    /**
     * {@inheritDoc}
     */
    public function getUriResolver(): ?ResolverInterface
    {
        return $this->resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function withUriResolver(?ResolverInterface $resolver): UriResolvableInterface
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
}
