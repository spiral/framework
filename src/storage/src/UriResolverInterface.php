<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\Exception\ResolveException;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Parser\UriInterface;
use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * @psalm-type UriLikeType = string | \Stringable | UriInterface | PsrUriInterface
 *
 * @see UriInterface
 * @see PsrUriInterface
 */
interface UriResolverInterface
{
    /**
     * Build real HTTP URLs list by list of "virtual" uris
     *
     * @param iterable<UriLikeType> $uris
     * @return iterable<string|\Stringable>
     * @throws ResolveException
     * @throws StorageException
     */
    public function resolveAll(iterable $uris): iterable;

    /**
     * Build real HTTP URL by "virtual" uris
     *
     * @param UriLikeType $uri
     * @return string|\Stringable
     * @throws ResolveException
     * @throws StorageException
     */
    public function resolve($uri);
}
