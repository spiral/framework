<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Autofill;
use Spiral\Router\Exception\ConstrainException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class ActionTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/home', new Action(TestController::class, 'test'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        self::assertSame(['action' => 'test'], $route->getDefaults());
    }

    public function testConstrains(): void
    {
        $route = new Route('/home', new Action(TestController::class, 'test'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        self::assertEquals(['action' => new Autofill('test')], $route->getUriHandler()->getConstrains());

        $route = new Route('/<action>', new Action(TestController::class, ['test', 'other']));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        self::assertSame(['action' => ['test', 'other']], $route->getUriHandler()->getConstrains());
    }

    public function testConstrainedAction(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/home', new Action(TestController::class, ['test', 'other']));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest('GET', ''));
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/test[/<action>]',
            new Action(TestController::class, ['test', 'other']),
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route = $route->withDefaults(['action' => 'test']);

        self::assertNull($route->match(new ServerRequest('GET', '')));
        self::assertNull($route->match(new ServerRequest('GET', new Uri('/test/something'))));
        self::assertNull($route->match(new ServerRequest('GET', new Uri('/test/tester'))));

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test'))));

        self::assertSame(['action' => 'test'], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test/'))));
        self::assertSame(['action' => 'test'], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test/test'))));
        self::assertSame(['action' => 'test'], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test/test/'))));
        self::assertSame(['action' => 'test'], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test/other'))));

        self::assertSame(['action' => 'other'], $match->getMatches());
    }
}
