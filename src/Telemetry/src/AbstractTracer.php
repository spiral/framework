<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Psr\Container\ContainerInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\Internal\CurrentTrace;

/**
 * @internal The component is under development.
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
abstract class AbstractTracer implements TracerInterface
{
    public function __construct(
        private readonly ?ScopeInterface $scope = new Container(),
    ) {
    }

    /**
     * @throws \Throwable
     */
    final protected function runScope(Span $span, callable $callback): mixed
    {
        $container = ContainerScope::getContainer();

        if ($container instanceof Container) {
            $invoker = $container;
            $binder = $container;
        } else {
            /** @var InvokerInterface $invoker */
            $invoker = $container->get(InvokerInterface::class);
            /** @var BinderInterface $binder */
            $binder = $container->get(BinderInterface::class);
        }

        $previous = $container->get(CurrentTrace::class);
        $binder->bindSingleton(CurrentTrace::class, new CurrentTrace($this, $span));
        try {
            return $invoker->invoke($callback);
        } finally {
            $binder->bindSingleton(CurrentTrace::class, $previous);
        }
    }
}
