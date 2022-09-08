<?php

declare(strict_types=1);

namespace Spiral\Distribution;

use Psr\Http\Message\UriInterface;

interface UriResolverInterface
{
    public function resolve(string $file): UriInterface;
}
