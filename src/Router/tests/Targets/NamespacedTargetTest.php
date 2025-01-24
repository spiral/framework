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

    public function testDefaultAction(): void
    {
        $route = new Route('/<controller>/<action>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        self::assertSame(['controller' => null, 'action' => null], $route->getDefaults());
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
            new Namespaced('Spiral\Router\Fixtures'),
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route = $route->withDefaults(['controller' => 'test']);

        self::assertNull($route->match(new ServerRequest('GET', '')));

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test'))));

        self::assertSame(['controller' => 'test', 'action' => null], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/test/action/'))));

        self::assertSame(['controller' => 'test', 'action' => 'action'], $match->getMatches());

        self::assertNotNull($match = $route->match(new ServerRequest('GET', new Uri('/other/action/'))));

        self::assertSame(['controller' => 'other', 'action' => 'action'], $match->getMatches());
    }

    #[DataProvider('defaultProvider')]
    public function testDefaults(string $pattern, string $uri, array $defaults): void
    {
        $route = new Route($pattern, new Namespaced('Spiral\Router\Fixtures'), $defaults);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $request = new ServerRequest('GET', new Uri($uri));

        $match = $route->match($request);
        self::assertNotNull($match);

        $values = $match->getMatches();
        self::assertNotNull($values['controller']);
        self::assertNotNull($values['action']);
    }
}
