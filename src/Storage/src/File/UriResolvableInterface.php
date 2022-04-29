<?php

declare(strict_types=1);

namespace Spiral\Storage\File;

use Psr\Http\Message\UriInterface;
use Spiral\Distribution\UriResolverInterface;

interface UriResolvableInterface extends EntryInterface
{
    /**
     * @param mixed ...$args An additional uri arguments
     */
    public function toUri(mixed ...$args): UriInterface;

    /**
     * @param mixed ...$args An additional uri arguments
     */
    public function toUriFrom(UriResolverInterface $resolver, mixed ...$args): UriInterface;
}
