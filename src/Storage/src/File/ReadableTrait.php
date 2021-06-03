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
use Spiral\Storage\BucketInterface;
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
     * {@see EntryInterface::getBucket()}
     */
    abstract public function getBucket(): BucketInterface;

    /**
     * {@inheritDoc}
     */
    public function exists(): bool
    {
        $bucket = $this->getBucket();

        return $bucket->exists($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(): string
    {
        $bucket = $this->getBucket();

        return $bucket->getContents($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getStream()
    {
        $bucket = $this->getBucket();

        return $bucket->getStream($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getLastModified(): int
    {
        $bucket = $this->getBucket();

        return $bucket->getLastModified($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): int
    {
        $bucket = $this->getBucket();

        return $bucket->getSize($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType(): string
    {
        $bucket = $this->getBucket();

        return $bucket->getMimeType($this->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(): string
    {
        $bucket = $this->getBucket();

        return $bucket->getVisibility($this->getPathname());
    }
}
