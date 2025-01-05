<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Spiral\Router\Route;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Diactoros\UriFactory;

class SubdomainTest extends TestCase
{
    public function testSubDomainWithoutAction(): void
    {
        $route = new Route(
            '//[<sub>.]site.com/foo',
            'test',
            ['sub' => 'subdomain']
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(new ServerRequest('GET', new Uri('http://site.com/foo')));
        self::assertSame(['sub' => 'subdomain'], $match->getMatches());

        $match = $route->match(new ServerRequest('GET', new Uri('http://bar.site.com/foo')));
        self::assertSame(['sub' => 'bar'], $match->getMatches());
    }

    public function testSubDomainWithAction(): void
    {
        $route = new Route(
            '//[<sub>.]site.com/foo[/<action>]',
            'test',
            ['sub' => 'subdomain']
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(new ServerRequest('GET', new Uri('http://site.com/foo/bar')));
        self::assertSame(['sub' => 'subdomain', 'action' => 'bar'], $match->getMatches());

        $match = $route->match(new ServerRequest('GET', new Uri('http://site.com/foo')));
        self::assertSame(['sub' => 'subdomain', 'action' => null], $match->getMatches());

        $match = $route->match(new ServerRequest('GET', new Uri('http://bar.site.com/foo')));
        self::assertSame(['sub' => 'bar', 'action' => null], $match->getMatches());

        $match = $route->match(new ServerRequest('GET', new Uri('http://bar.site.com/foo/bar')));
        self::assertSame(['sub' => 'bar', 'action' => 'bar'], $match->getMatches());
    }
}
