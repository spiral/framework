<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Boot\BootloadManager\InvokerStrategyInterface;
use Spiral\Core\Container;

abstract class BaseTestCase extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(EnvironmentInterface::class, new Environment());
        $this->container->bind(InvokerStrategyInterface::class, DefaultInvokerStrategy::class);
        $this->container->bind(InitializerInterface::class, Initializer::class);
    }
}
