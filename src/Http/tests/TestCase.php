<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
        $this->container->bind(TracerInterface::class, new NullTracer($this->container));
    }
}
