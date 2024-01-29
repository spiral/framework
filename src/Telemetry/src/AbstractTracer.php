<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

/**
 * @internal The component is under development.
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
abstract class AbstractTracer implements TracerInterface
{
    public function __construct(
        #[Proxy] private readonly ?ScopeInterface $scope = new Container(),
    ) {
    }

    /**
     * @throws \Throwable
     */
    final protected function runScope(Span $span, callable $callback): mixed
    {
        // TODO: Can we remove this scope?
        return $this->scope->runScope([
            SpanInterface::class => $span,
            TracerInterface::class => $this,
        ], static fn (InvokerInterface $invoker): mixed => $invoker->invoke($callback));
    }
}
