<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Psr\Http\Message\UriInterface;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Storage\ReadableInterface;
use Spiral\Storage\Storage\WritableInterface;

/**
 * @template-implements \IteratorAggregate<string, StorageInterface>
 *
 * @psalm-type IdType = string | UriInterface | \Stringable
 * @see UriInterface
 */
interface StorageInterface extends
    ReadableInterface,
    WritableInterface,
    \IteratorAggregate,
    \Countable
{
    /**
     * @param string|null $name
     * @throws InvalidArgumentException
     */
    public function bucket(string $name = null): BucketInterface;

    /**
     * @param IdType $id
     * @throws InvalidArgumentException
     */
    public function file($id): FileInterface;

    public function withDefault(string $name): self;
}
