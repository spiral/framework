<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Distribution\ResolverInterface;
use Spiral\Storage\File\ReadableTrait;
use Spiral\Storage\File\UriResolvableTrait;
use Spiral\Storage\File\WritableTrait;

final class File implements FileInterface
{
    use UriResolvableTrait;
    use ReadableTrait;
    use WritableTrait;

    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * @var string
     */
    private string $pathname;

    /**
     * @var ResolverInterface|null
     */
    private $resolver;

    /**
     * @param StorageInterface $storage
     * @param string $pathname
     * @param ResolverInterface|null $resolver
     */
    public function __construct(StorageInterface $storage, string $pathname, ResolverInterface $resolver = null)
    {
        $this->storage = $storage;
        $this->pathname = $pathname;
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->getPathname();
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
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return ResolverInterface|null
     */
    protected function getResolver(): ?ResolverInterface
    {
        return $this->resolver;
    }
}
