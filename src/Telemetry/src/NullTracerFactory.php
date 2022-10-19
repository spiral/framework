<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Container;
use Spiral\Core\ScopeInterface;

final class NullTracerFactory implements TracerFactoryInterface
{
    public function __construct(
        private readonly ?ScopeInterface $scope = new Container(),
    ) {
    }

    public function make(array $context = []): TracerInterface
    {
        return new NullTracer($this->scope);
    }
}
