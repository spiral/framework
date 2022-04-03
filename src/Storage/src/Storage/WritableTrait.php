<?php

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

    public function create(string|\Stringable $id, array $config = []): FileInterface
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->create($pathname, $config);
    }

    public function write(string|\Stringable $id, mixed $content, array $config = []): FileInterface
    {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->write($pathname, $content, $config);
    }

    public function setVisibility(
        string|\Stringable $id,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        [$name, $pathname] = $this->parseUri($id);

        return $this->bucket($name)->setVisibility($pathname, $visibility);
    }

    public function copy(
        string|\Stringable $source,
        string|\Stringable $destination,
        array $config = []
    ): FileInterface {
        [$sourceName, $sourcePathname] = $this->parseUri($source);
        [$destName, $destPathname] = $this->parseUri($destination, false);

        $sourceStorage = $this->bucket($sourceName);
        $destStorage = $destName ? $this->bucket($destName) : null;

        return $sourceStorage->copy($sourcePathname, $destPathname, $destStorage, $config);
    }

    public function move(
        string|\Stringable $source,
        string|\Stringable $destination,
        array $config = []
    ): FileInterface {
        [$sourceName, $sourcePathname] = $this->parseUri($source);
        [$destName, $destPathname] = $this->parseUri($destination, false);

        $sourceStorage = $this->bucket($sourceName);
        $destStorage = $destName ? $this->bucket($destName) : null;

        return $sourceStorage->move($sourcePathname, $destPathname, $destStorage, $config);
    }

    public function delete(string|\Stringable $id, bool $clean = false): void
    {
        [$name, $pathname] = $this->parseUri($id);

        $bucket = $this->bucket($name);

        $bucket->delete($pathname, $clean);
    }

    /**
     * {@see Storage::parseUri()}
     */
    abstract protected function parseUri(string|\Stringable $uri, bool $withScheme = true): array;
}
