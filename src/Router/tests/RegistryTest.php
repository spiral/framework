<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;

final class RegistryTest extends BaseTestCase
{
    public function testSameGroup(): void
    {
        $registry = new GroupRegistry($this->getContainer());
        $router = new Router('/', new UriHandler(new Psr17Factory()), $this->getContainer());
        $this->getContainer()->bind(RouterInterface::class, $router);

        $group = $registry->getGroup('default');
        $this->assertSame($group, $registry->getGroup('default'));
    }
}
