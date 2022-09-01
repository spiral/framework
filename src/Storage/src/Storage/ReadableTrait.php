<?php

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Psr\Http\Message\UriInterface;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

trait ReadableTrait
{
    /**
     * {@see StorageInterface::bucket()}
     */
    abstract public function bucket(string $name = null): BucketInterface;

    public function getContents(string|\Stringable $id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getContents($pathname);
    }

    public function getStream(string|\Stringable $id)
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getStream($pathname);
    }

    public function exists(string|\Stringable $id): bool
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->exists($pathname);
    }

    /**
     * @return positive-int|0
     */
    public function getLastModified(string|\Stringable $id): int
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getLastModified($pathname);
    }

    /**
     * @return positive-int|0
     */
    public function getSize(string|\Stringable $id): int
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getSize($pathname);
    }

    public function getMimeType(string|\Stringable $id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getMimeType($pathname);
    }

    /**
     * @return Visibility::VISIBILITY_*
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(string|\Stringable $id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getVisibility($pathname);
    }

    /**
     * {@see Storage::parseUri()}
     */
    abstract protected function parseUri(string|\Stringable $uri, bool $withScheme = true): array;
}
