<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Route;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;

class RouteTest extends TestCase
{
    public function testPrefix(): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));
        $this->assertSame('', $route->getUriHandler()->getPrefix());

        $route2 = $route->withUriHandler($route->getUriHandler()->withPrefix('/something'));
        $this->assertSame('/something', $route2->getUriHandler()->getPrefix());
        $this->assertSame('', $route->getUriHandler()->getPrefix());
    }

    public function testContainerException(): void
    {
        $this->expectException(RouteException::class);

        $route = new Route('/action', Call::class);
        $route->handle(new ServerRequest('GET', ''));
    }
}
