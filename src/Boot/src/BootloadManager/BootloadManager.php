<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Core\InvokerInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Core\ScopeInterface;

/**
 * @deprecated Use the {@see StrategyBasedBootloadManager} instead. Will be removed in v4.0
 */
final class BootloadManager extends AbstractBootloadManager
{
    private readonly InvokerStrategyInterface $invokerStrategy;

    public function __construct(
        ScopeInterface $scope,
        private readonly InvokerInterface $invoker,
        private readonly ResolverInterface $resolver,
        InitializerInterface $initializer,
        ?InvokerStrategyInterface $invokerStrategy = null
    ) {
        parent::__construct($scope, $initializer);

        $this->invokerStrategy = $invokerStrategy ?? new DefaultInvokerStrategy(...$this->resolver->resolveArguments(
            (new \ReflectionClass(DefaultInvokerStrategy::class))->getConstructor()
        ));
    }

    /**
     * Bootload all given bootloaders.
     *
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     *
     * @throws \Throwable
     */
    protected function boot(array $classes, array $bootingCallbacks, array $bootedCallbacks): void
    {
        $this->invokerStrategy->invokeBootloaders($classes, $bootingCallbacks, $bootedCallbacks);
    }
}
