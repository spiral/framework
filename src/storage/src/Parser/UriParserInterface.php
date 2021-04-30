<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Parser;

use Spiral\Storage\Exception\UriException;

use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * @psalm-type UriLikeType = string | \Stringable | UriInterface | PsrUriInterface
 * @see PsrUriInterface
 */
interface UriParserInterface
{
    /**
     * Parse uri to URI value object that representing a FileSystem URI.
     *
     * @param UriLikeType $uri
     * @return UriInterface
     * @throws UriException
     */
    public function parse($uri): UriInterface;
}
