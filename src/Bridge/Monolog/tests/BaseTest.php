<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;

abstract class BaseTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(EnvironmentInterface::class, new Environment());
    }
}
