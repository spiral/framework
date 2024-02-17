<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Scope;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

abstract class ScopedTestCase extends TestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(TracerInterface::class, new NullTracer($this->container));
    }

    protected function runTest(): mixed
    {
        return $this->container->runScope(new Scope('http'), function (Container $container): mixed {
            $this->container = $container;
            return parent::runTest();
        });
    }
}
