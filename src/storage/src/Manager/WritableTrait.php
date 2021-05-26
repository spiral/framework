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
use Spiral\Storage\FileInterface;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin WritableInterface
 * @psalm-import-type UriType from WritableInterface
 */
trait WritableTrait
{
    /**
     * {@see ManagerInterface::storage()}
     */
    abstract public function storage(string $name = null): StorageInterface;

    /**
     * {@see Manager::parseUri()}
     */
    abstract protected function parseUri($uri, bool $withScheme = true): array;

    /**
     * {@inheritDoc}
     */
    public function create($uri, array $config = []): FileInterface
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->create($pathname, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function write($uri, $content, array $config = []): FileInterface
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->write($pathname, $content, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function setVisibility(
        $uri,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        return $storage->setVisibility($pathname, $visibility);
    }

    /**
     * {@inheritDoc}
     */
    public function copy($source, $destination, array $config = []): FileInterface
    {
        [$sourceName, $sourcePathname] = $this->parseUri($source);
        [$destName, $destPathname] = $this->parseUri($destination, false);

        $sourceStorage = $this->storage($sourceName);
        $destStorage = $destName ? $this->storage($destName) : null;

        return $sourceStorage->copy($sourcePathname, $destPathname, $destStorage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function move($source, $destination, array $config = []): FileInterface
    {
        [$sourceName, $sourcePathname] = $this->parseUri($source);
        [$destName, $destPathname] = $this->parseUri($destination, false);

        $sourceStorage = $this->storage($sourceName);
        $destStorage = $destName ? $this->storage($destName) : null;

        return $sourceStorage->move($sourcePathname, $destPathname, $destStorage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($uri, bool $clean = false): void
    {
        [$name, $pathname] = $this->parseUri($uri);

        $storage = $this->storage($name);

        $storage->delete($pathname, $clean);
    }
}
