<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Diactoros;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\Uri;

final class UriFactory implements UriFactoryInterface
{
    /**
     * @param string $uri
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
