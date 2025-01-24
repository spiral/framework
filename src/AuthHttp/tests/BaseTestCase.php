<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

abstract class BaseTestCase extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);

        $this->container->bind(
            TracerInterface::class,
            new NullTracer($this->container),
        );
    }
}
