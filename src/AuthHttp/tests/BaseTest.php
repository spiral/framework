<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

abstract class BaseTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->container->bind(
            TracerInterface::class,
            new NullTracer($this->container)
        );
    }
}
