<?php

declare(strict_types=1);

namespace Spiral\Storage;

use Psr\Http\Message\UriInterface;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Storage\ReadableInterface;
use Spiral\Storage\Storage\WritableInterface;

/**
 * @extends \IteratorAggregate<string, BucketInterface>
 *
 * @see UriInterface
 */
interface StorageInterface extends
    ReadableInterface,
    WritableInterface,
    \IteratorAggregate,
    \Countable
{
    /**
     * @throws InvalidArgumentException
     */
    public function bucket(string $name = null): BucketInterface;

    /**
     * @throws InvalidArgumentException
     */
    public function file(string|\Stringable $id): FileInterface;

    public function withDefault(string $name): self;
}
