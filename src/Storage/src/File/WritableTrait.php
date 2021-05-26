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
use Spiral\Storage\FileInterface;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin WritableInterface
 */
trait WritableTrait
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
    public function create(array $config = []): FileInterface
    {
        if (! $this->exists()) {
            return $this->write('', $config);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function write($content, array $config = []): FileInterface
    {
        $storage = $this->getStorage();

        return $storage->write($this->getPathname(), $content, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function setVisibility(
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        $storage = $this->getStorage();

        return $storage->setVisibility($this->getPathname(), $visibility);
    }

    /**
     * {@inheritDoc}
     */
    public function copy(string $pathname, StorageInterface $storage = null, array $config = []): FileInterface
    {
        $source = $this->getStorage();

        return $source->copy($this->getPathname(), $pathname, $storage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function move(string $pathname, StorageInterface $storage = null, array $config = []): FileInterface
    {
        $source = $this->getStorage();

        return $source->move($this->getPathname(), $pathname, $storage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(bool $clean = false): void
    {
        $source = $this->getStorage();

        $source->delete($this->getPathname(), $clean);
    }
}
