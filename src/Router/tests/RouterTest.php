<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;

class RouterTest extends BaseTest
{
    public function testGetRoutes(): void
    {
        $router = $this->makeRouter();

        $router->setRoute('name', new Route('/', Call::class));
        $this->assertCount(1, $router->getRoutes());
    }

    public function testDefault(): void
    {
        $router = $this->makeRouter();

        $router->setRoute('name', new Route('/', Call::class));
        $router->setDefault(new Route('/', Call::class));

        $this->assertCount(2, $router->getRoutes());
    }

    public function testCastError(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->uri('name/?broken');
    }
}
