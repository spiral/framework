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
use Spiral\Storage\BucketInterface;
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
     * {@see EntryInterface::getBucket()}
     */
    abstract public function getBucket(): BucketInterface;

    /**
     * {@inheritDoc}
     */
    public function create(array $config = []): FileInterface
    {
        $bucket = $this->getBucket();

        return $bucket->create($this->getPathname(), $config);
    }

    /**
     * {@inheritDoc}
     */
    public function write($content, array $config = []): FileInterface
    {
        $bucket = $this->getBucket();

        return $bucket->write($this->getPathname(), $content, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function setVisibility(
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        $bucket = $this->getBucket();

        return $bucket->setVisibility($this->getPathname(), $visibility);
    }

    /**
     * {@inheritDoc}
     */
    public function copy(string $pathname, BucketInterface $storage = null, array $config = []): FileInterface
    {
        $source = $this->getBucket();

        return $source->copy($this->getPathname(), $pathname, $storage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function move(string $pathname, BucketInterface $storage = null, array $config = []): FileInterface
    {
        $source = $this->getBucket();

        return $source->move($this->getPathname(), $pathname, $storage, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(bool $clean = false): void
    {
        $source = $this->getBucket();

        $source->delete($this->getPathname(), $clean);
    }
}
