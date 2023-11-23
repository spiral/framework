<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bindSingleton(EnvironmentInterface::class, Environment::class, true);
    }

    public function getBootloadManager(): StrategyBasedBootloadManager
    {
        $initializer = new Initializer($this->container, $this->container);

        return new StrategyBasedBootloadManager(
            new DefaultInvokerStrategy($initializer, $this->container, $this->container),
            $this->container,
            $initializer
        );
    }
}
