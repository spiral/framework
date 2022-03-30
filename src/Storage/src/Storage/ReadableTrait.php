<?php

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Psr\Http\Message\UriInterface;
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

    public function getContents(string|UriInterface|\Stringable $id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getContents($pathname);
    }

    public function getStream(string|UriInterface|\Stringable $id)
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getStream($pathname);
    }

    public function exists(string|UriInterface|\Stringable $id): bool
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->exists($pathname);
    }

    public function getLastModified(string|UriInterface|\Stringable $id): int
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getLastModified($pathname);
    }

    public function getSize(string|UriInterface|\Stringable $id): int
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getSize($pathname);
    }

    public function getMimeType(string|UriInterface|\Stringable $id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getMimeType($pathname);
    }

    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(string|UriInterface|\Stringable $id): string
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->getVisibility($pathname);
    }

    /**
     * {@see Storage::parseUri()}
     */
    abstract protected function parseUri(string|UriInterface|\Stringable $uri, bool $withScheme = true): array;
}
