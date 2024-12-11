<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Stub;

use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\RouteCollection;

class TestLoader implements LoaderInterface
{
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return new RouteCollection();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'yaml';
    }
}
