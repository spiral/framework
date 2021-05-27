<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Distribution\UriResolverInterface;
use Spiral\Storage\File\ReadableTrait;
use Spiral\Storage\File\UriResolvableTrait;
use Spiral\Storage\File\WritableTrait;

final class File implements FileInterface
{
    use UriResolvableTrait;
    use ReadableTrait;
    use WritableTrait;

    /**
     * @var BucketInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $pathname;

    /**
     * @var UriResolverInterface|null
     */
    private $resolver;

    /**
     * @param BucketInterface $storage
     * @param string $pathname
     * @param UriResolverInterface|null $resolver
     */
    public function __construct(BucketInterface $storage, string $pathname, UriResolverInterface $resolver = null)
    {
        $this->storage = $storage;
        $this->pathname = $pathname;
        $this->resolver = $resolver;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        $name = $this->storage->getName();

        if ($name === null) {
            return $this->getPathname();
        }

        return \sprintf('%s://%s', $name, $this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getPathname(): string
    {
        return $this->pathname;
    }

    /**
     * {@inheritDoc}
     */
    public function getBucket(): BucketInterface
    {
        return $this->storage;
    }

    /**
     * @return UriResolverInterface|null
     */
    protected function getResolver(): ?UriResolverInterface
    {
        return $this->resolver;
    }
}
