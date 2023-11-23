<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Core\ScopeInterface;

final class StrategyBasedBootloadManager extends AbstractBootloadManager
{
    public function __construct(
        private readonly InvokerStrategyInterface $invoker,
        ScopeInterface $scope,
        InitializerInterface $initializer
    ) {
        parent::__construct($scope, $initializer);
    }

    /**
     * Bootload all given bootloaders.
     *
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     *
     * @throws \Throwable
     */
    protected function boot(
        array $classes,
        array $bootingCallbacks,
        array $bootedCallbacks,
        bool $useConfig = true
    ): void {
        /** @psalm-suppress TooManyArguments */
        $this->invoker->invokeBootloaders($classes, $bootingCallbacks, $bootedCallbacks, $useConfig);
    }
}
