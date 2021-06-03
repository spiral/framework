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
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin ReadableInterface
 */
trait ReadableTrait
{
    /**
     * {@see StorageInterface::bucket()}
     */
    abstract public function bucket(string $name = null): BucketInterface;

    /**
     * {@inheritDoc}
     */
    public function getContents($id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->getContents($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getStream($id)
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->getStream($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($id): bool
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->exists($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getLastModified($id): int
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->getLastModified($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getSize($id): int
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->getSize($pathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType($id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->getMimeType($pathname);
    }

    /**
     * {@inheritDoc}
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility($id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        return $bucket->getVisibility($pathname);
    }

    /**
     * {@see Storage::parseUri()}
     */
    abstract protected function parseUri($uri, bool $withScheme = true): array;
}
