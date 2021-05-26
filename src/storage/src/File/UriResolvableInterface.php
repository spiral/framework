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
use Spiral\Distribution\ResolverInterface;

interface UriResolvableInterface extends EntryInterface
{
    /**
     * @return UriInterface
     */
    public function toUri(): UriInterface;

    /**
     * @param ResolverInterface $resolver
     * @return UriInterface
     */
    public function toUriFrom(ResolverInterface $resolver): UriInterface;
}
