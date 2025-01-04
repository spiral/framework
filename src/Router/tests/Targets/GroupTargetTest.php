<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Exception\ConstrainException;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class GroupTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/<controller>/<action>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        self::assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    public function testConstrainedController(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<action>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest('GET', ''));
    }

    public function testConstrainedAction(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<controller>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));
        $route->match(new ServerRequest('GET', ''));
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/<controller>[/<action>]',
            new Group(['test' => TestController::class])
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route = $route->withDefaults(['controller' => 'test']);

        self::assertNull($route->match(new ServerRequest('GET', '')));

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test'))));

        self::assertSame(['controller' => 'test', 'action' => null], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test/action/'))));

        self::assertSame(['controller' => 'test', 'action' => 'action'], $match->getMatches());

        self::assertNull($match = $route->match(new ServerRequest('GET', new Uri('/other/action/'))));

        self::assertNull($match = $route->match(new ServerRequest('GET', new Uri('/other'))));
    }
}
