<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

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

        try {
            $prevSpan = $container->get(SpanInterface::class);
        } catch (\Throwable) {
            $prevSpan = null;
        }

        $binder->bindSingleton(SpanInterface::class, $span);
        try {
            return $invoker->invoke($callback);
        } finally {
            $prevSpan === null
                ? $binder->removeBinding(SpanInterface::class)
                : $binder->bindSingleton(SpanInterface::class, $prevSpan);
        }
    }
}
