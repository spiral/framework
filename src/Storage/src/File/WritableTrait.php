<?php

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

    public function create(array $config = []): FileInterface
    {
        return $this->getBucket()->create($this->getPathname(), $config);
    }

    public function write(mixed $content, array $config = []): FileInterface
    {
        return $this->getBucket()->write($this->getPathname(), $content, $config);
    }

    public function setVisibility(
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        return $this->getBucket()->setVisibility($this->getPathname(), $visibility);
    }

    public function copy(string $pathname, BucketInterface $storage = null, array $config = []): FileInterface
    {
        return $this->getBucket()->copy($this->getPathname(), $pathname, $storage, $config);
    }

    public function move(string $pathname, BucketInterface $storage = null, array $config = []): FileInterface
    {
        return $this->getBucket()->move($this->getPathname(), $pathname, $storage, $config);
    }

    public function delete(bool $clean = false): void
    {
        $source = $this->getBucket();

        $source->delete($this->getPathname(), $clean);
    }
}
