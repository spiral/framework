<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Cocur\Slugify\Slugify;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Tests\Router\Diactoros\ResponseFactory;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;

abstract class BaseTest extends TestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(ResponseFactoryInterface::class, new ResponseFactory(new HttpConfig(['headers' => []])));
        $this->container->bind(UriFactoryInterface::class, new UriFactory());

        $this->container->bind(CoreInterface::class, Core::class);
    }

    protected function makeRouter(string $basePath = ''): RouterInterface
    {
        return new Router($basePath, new UriHandler(
            new UriFactory(),
            new Slugify()
        ), $this->container);
    }
}
