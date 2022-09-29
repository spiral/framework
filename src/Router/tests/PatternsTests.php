<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Spiral\Router\Route;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Diactoros\UriFactory;

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
            'type' => '0'
        ], $match->getMatches());
    }
}
