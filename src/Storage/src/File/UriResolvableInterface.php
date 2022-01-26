<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\File;

use Psr\Http\Message\UriInterface;
use Spiral\Distribution\UriResolverInterface;

interface UriResolvableInterface extends EntryInterface
{
    /**
     * @param mixed ...$args An additional uri arguments
     */
    public function toUri(...$args): UriInterface;

    /**
     * @param mixed ...$args An additional uri arguments
     */
    public function toUriFrom(UriResolverInterface $resolver, ...$args): UriInterface;
}
