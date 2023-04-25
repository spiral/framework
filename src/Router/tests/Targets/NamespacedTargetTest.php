<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Targets;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Router\Exception\ConstrainException;
use Spiral\Router\Route;
use Spiral\Router\Target\Namespaced;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class NamespacedTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/<controller>/<action>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    public function testConstrainedController(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<action>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest('GET', ''));
    }

    public function testConstrainedAction(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<controller>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest('GET', ''));
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/<controller>[/<action>]',
            new Namespaced('Spiral\Router\Fixtures')
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route = $route->withDefaults(['controller' => 'test']);

        $this->assertNull($route->match(new ServerRequest('GET', '')));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test')))
        );

        $this->assertSame(['controller' => 'test', 'action' => null], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/test/action/')))
        );

        $this->assertSame(['controller' => 'test', 'action' => 'action'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest('GET', new Uri('/other/action/')))
        );

        $this->assertSame(['controller' => 'other', 'action' => 'action'], $match->getMatches());
    }

    #[DataProvider('defaultProvider')]
    public function testDefaults(string $pattern, string $uri, array $defaults): void
    {
        $route = new Route($pattern, new Namespaced('Spiral\Router\Fixtures'), $defaults);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $request = new ServerRequest('GET', new Uri($uri));

        $match = $route->match($request);
        $this->assertNotNull($match);

        $values = $match->getMatches();
        $this->assertNotNull($values['controller']);
        $this->assertNotNull($values['action']);
    }

    public static function defaultProvider(): \Traversable
    {
        yield ['<controller>[/<action>]', '/home', ['controller' => 'home', 'action' => 'test']];
        yield ['<controller>[/<action>]', '/home/test', ['controller' => 'home', 'action' => 'test']];
        yield ['/<controller>[/<action>]', '/home', ['controller' => 'home', 'action' => 'test']];
        yield ['/<controller>[/<action>]', '/home/test', ['controller' => 'home', 'action' => 'test']];

        yield ['[<controller>[/<action>]]', '/home', ['controller' => 'home', 'action' => 'test']];
        yield ['[<controller>[/<action>]]', '/home/test', ['controller' => 'home', 'action' => 'test']];
        yield ['[<controller>[/<action>]]', '/', ['controller' => 'home', 'action' => 'test']];
        yield ['[<controller>[/<action>]]', '', ['controller' => 'home', 'action' => 'test']];

        yield ['[/<controller>[/<action>]]', '/home', ['controller' => 'home', 'action' => 'test']];
        yield ['[/<controller>[/<action>]]', '/home/test', ['controller' => 'home', 'action' => 'test']];
        yield ['[/<controller>[/<action>]]', '/', ['controller' => 'home', 'action' => 'test']];
        yield ['[/<controller>[/<action>]]', '', ['controller' => 'home', 'action' => 'test']];
    }
}
