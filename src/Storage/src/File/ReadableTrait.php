<?php

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

    public function exists(): bool
    {
        return $this->getBucket()->exists($this->getPathname());
    }

    public function getContents(): string
    {
        return $this->getBucket()->getContents($this->getPathname());
    }

    public function getStream()
    {
        return $this->getBucket()->getStream($this->getPathname());
    }

    /**
     * @return positive-int
     */
    public function getLastModified(): int
    {
        return $this->getBucket()->getLastModified($this->getPathname());
    }

    /**
     * @return positive-int|0
     */
    public function getSize(): int
    {
        return $this->getBucket()->getSize($this->getPathname());
    }

    public function getMimeType(): string
    {
        return $this->getBucket()->getMimeType($this->getPathname());
    }

    /**
     * @return Visibility::VISIBILITY_*
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(): string
    {
        return $this->getBucket()->getVisibility($this->getPathname());
    }
}
