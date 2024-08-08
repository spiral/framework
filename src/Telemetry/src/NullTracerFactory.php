<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container;
use Spiral\Core\ScopeInterface;

/**
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
final class NullTracerFactory implements TracerFactoryInterface
{
    public function __construct(
        #[Proxy] private readonly ?ScopeInterface $scope = new Container(),
    ) {
    }

    public function make(array $context = []): TracerInterface
    {
        return new NullTracer($this->scope);
    }
}
