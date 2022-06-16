<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;

final class RegistryTest extends BaseTest
{
    public function testSameGroup(): void
    {
        $registry = new GroupRegistry($this->container);
        $router = new Router('/', new UriHandler(new Psr17Factory()), $this->container);
        $this->container->bind(RouterInterface::class, $router);

        $group = $registry->getGroup('default');
        $this->assertSame($group, $registry->getGroup('default'));
    }
}
