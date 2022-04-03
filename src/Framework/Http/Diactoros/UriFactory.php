<?php

declare(strict_types=1);

namespace Spiral\Http\Diactoros;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Laminas\Diactoros\Uri;

final class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
