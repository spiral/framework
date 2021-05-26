<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\File;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin ReadableInterface
 */
trait ReadableTrait
{
    /**
     * {@see EntryInterface::getPathname()}
     */
    abstract public function getPathname(): string;

    /**
     * {@see EntryInterface::getStorage()}
     */
    abstract public function getStorage(): StorageInterface;

    /**
     * {@inheritDoc}
     */
    public function exists(): bool
    {
        $storage = $this->getStorage();

        return $storage->exists($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(): string
    {
        $storage = $this->getStorage();

        return $storage->getContents($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getStream()
    {
        $storage = $this->getStorage();

        return $storage->getStream($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getLastModified(): int
    {
        $storage = $this->getStorage();

        return $storage->getLastModified($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): int
    {
        $storage = $this->getStorage();

        return $storage->getSize($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType(): string
    {
        $storage = $this->getStorage();

        return $storage->getMimeType($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(): string
    {
        $storage = $this->getStorage();

        return $storage->getVisibility($this->getPathname());
    }
}
