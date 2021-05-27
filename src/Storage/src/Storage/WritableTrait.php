<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\FileInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin WritableInterface
 */
trait WritableTrait
{
    /**
     * {@see StorageInterface::bucket()}
     */
    abstract public function bucket(string $name = null): BucketInterface;

    /**
     * {@inheritDoc}
     */
    public function create($id, array $config = []): FileInterface
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->create($pathname, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function write($id, $content, array $config = []): FileInterface
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->write($pathname, $content, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function setVisibility(
        $id,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->setVisibility($pathname, $visibility);
    }

    /**
     * {@inheritDoc}
     */
    public function copy($source, $destination, array $config = []): FileInterface
    {
        [$sourceName, $sourcePathname] = $this->parseUri($source);
        [$destName, $destPathname] = $this->parseUri($destination, false);

        $sourceStorage = $this->bucket($sourceName);
        $destStorage = $destName ? $this->bucket($destName) : null;

        return $sourceStorage->copy($sourcePathname, $destPathname, $destStorage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function move($source, $destination, array $config = []): FileInterface
    {
        [$sourceName, $sourcePathname] = $this->parseUri($source);
        [$destName, $destPathname] = $this->parseUri($destination, false);

        $sourceStorage = $this->bucket($sourceName);
        $destStorage = $destName ? $this->bucket($destName) : null;

        return $sourceStorage->move($sourcePathname, $destPathname, $destStorage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, bool $clean = false): void
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        $bucket->delete($pathname, $clean);
    }

    /**
     * {@see Storage::parseUri()}
     */
    abstract protected function parseUri($uri, bool $withScheme = true): array;
}
