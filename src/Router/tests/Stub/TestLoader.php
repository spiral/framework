<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Stub;

use Spiral\Router\Loader\LoaderInterface;

class TestLoader implements LoaderInterface
{
    public function load(mixed $resource, string $type = null): mixed
    {
        return 'test';
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return
            \is_string($resource) &&
            \pathinfo($resource, \PATHINFO_EXTENSION) === 'yaml' &&
            (!$type || $type === 'yaml');
    }
}
