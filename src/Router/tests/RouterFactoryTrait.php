<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Cocur\Slugify\Slugify;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\Container;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;
use Spiral\Telemetry\NullTracer;
use Spiral\Tests\Router\Diactoros\UriFactory;

trait RouterFactoryTrait
{
    abstract public function getContainer(): Container;

    protected function makeRouter(string $basePath = '', ?EventDispatcherInterface $dispatcher = null): RouterInterface
    {
        $container = $this->getContainer();
        return new Router(
            $basePath,
            new UriHandler(
                new UriFactory(),
                new Slugify(),
            ),
            $container,
            $dispatcher,
            new NullTracer($container),
        );
    }
}
