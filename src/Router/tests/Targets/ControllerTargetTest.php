<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Route;
use Spiral\Router\Target\Controller;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class ControllerTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/home[/<action>]', new Controller(TestController::class));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['action' => null], $route->getDefaults());
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/test[/<action>]',
            new Controller(TestController::class)
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertNull($route->match(new ServerRequest('GET', '')));
        $this->assertNotNull($route->match(new ServerRequest('GET', new Uri('/test/something'))));
        $this->assertNotNull($route->match(new ServerRequest('GET', new Uri('/test/tester'))));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test')))
        );

        $this->assertSame(['action' => null], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test/')))
        );
        $this->assertSame(['action' => null], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test/test')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test/test/')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test/other')))
        );

        $this->assertSame(['action' => 'other'], $match->getMatches());
    }
}
