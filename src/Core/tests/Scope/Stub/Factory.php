<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

use Psr\Container\ContainerInterface;

class Factory
{
    public function __construct(
        protected ContainerInterface $container,
    ) {
    }

    public function make(string $key): mixed
    {
        return $this->container->get($key);
    }
}
