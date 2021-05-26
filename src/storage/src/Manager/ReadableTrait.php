<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Manager;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Manager;
use Spiral\Storage\ManagerInterface;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin ReadableInterface
 * @psalm-import-type UriType from ReadableInterface
 */
trait ReadableTrait
{
    /**
     * {@see ManagerInterface::storage()}
     */
    abstract public function storage(string $name = null): StorageInterface;

    /**
     * {@inheritDoc}
     */
    public function getContents($uri): string
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->getContents($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getStream($uri)
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->getStream($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($uri): bool
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->exists($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getLastModified($uri): int
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->getLastModified($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getSize($uri): int
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->getSize($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType($uri): string
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->getMimeType($pathname);
    }

    /**
     * {@inheritDoc}
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility($uri): string
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->getVisibility($pathname);
    }

    /**
     * {@see Manager::parseUri()}
     */
    abstract protected function parseUri($uri, bool $withScheme = true): array;
}
