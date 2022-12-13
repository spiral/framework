<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Spiral\Router\Registry\DefaultPatternRegistry;
use Spiral\Router\Route;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\InArrayPattern;

class PatternsTests extends TestCase
{
    public function testDigitWithZeroValue(): void
    {
        $route = new Route(
            '/statistics/set/<moduleType:\d+>/<moduleId:\d+>/<type:\d+>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(new ServerRequest('GET', new Uri('http://site.com/statistics/set/10/285/0')));

        $this->assertSame([
            'moduleType' => '10',
            'moduleId' => '285',
            'type' => '0',
        ], $match->getMatches());
    }

    public function testIntPatternWithValidValue(): void
    {
        $route = new Route(
            '/users/<int:int>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/1'))
        );

        $this->assertSame([
            'int' => '1',
        ], $match->getMatches());
    }

    public function testIntPatternWithInvalidValue(): void
    {
        $route = new Route(
            '/users/<int:int>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/1b'))
        );

        $this->assertNull($match);
    }

    public function testIntegerPatternWithValidValue(): void
    {
        $route = new Route(
            '/users/<integer:integer>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/1'))
        );

        $this->assertSame([
            'integer' => '1',
        ], $match->getMatches());
    }

    public function testIntegerPatternWithInvalidValue(): void
    {
        $route = new Route(
            '/users/<integer:integer>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/1b'))
        );

        $this->assertNull($match);
    }

    public function testUuidPatternWithValidValue(): void
    {
        $route = new Route(
            '/users/<uuid:uuid>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/34f7b660-7ad0-11ed-a1eb-0242ac120002'))
        );

        $this->assertSame([
            'uuid' => '34f7b660-7ad0-11ed-a1eb-0242ac120002',
        ], $match->getMatches());
    }

    public function testUuidPatternWithInvalidValue(): void
    {
        $route = new Route(
            '/users/<uuid:uuid>',
            'test'
        );

        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/34f7b660-7ad0'))
        );

        $this->assertNull($match);
    }

    public function testCustomPattern(): void
    {
        $route = new Route(
            '/users/<uuid:foo>',
            'test'
        );

        $registry = new DefaultPatternRegistry();

        $registry->register(
            'foo',
            '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}'
        );
        $registry->register(
            'bar',
            '[0-9]+'
        );

        $route = $route->withUriHandler(
            new UriHandler(
                new UriFactory(),
                patternRegistry: $registry
            )
        );

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/34f7b660-7ad0-11ed-a1eb-0242ac222222'))
        );

        $this->assertSame([
            'uuid' => '34f7b660-7ad0-11ed-a1eb-0242ac222222',
        ], $match->getMatches());
    }

    public function testCustomStringablePattern(): void
    {
        $route = new Route(
            '/users/<name:in_array>',
            'test'
        );

        $registry = new DefaultPatternRegistry();

        $registry->register(
            'in_array',
            new InArrayPattern(['foo', 'bar'])
        );
        $registry->register(
            'foo',
            '[0-9]+'
        );

        $route = $route->withUriHandler(
            new UriHandler(
                new UriFactory(),
                patternRegistry: $registry
            )
        );

        $match = $route->match(
            new ServerRequest('GET', new Uri('http://site.com/users/foo'))
        );

        $this->assertSame([
            'name' => 'foo',
        ], $match->getMatches());
    }
}
