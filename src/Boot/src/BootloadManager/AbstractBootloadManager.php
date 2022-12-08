<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Boot\BootloadManagerInterface;
use Spiral\Core\Container;
use Spiral\Core\ScopeInterface;

/**
 * Provides ability to bootload service providers.
 */
abstract class AbstractBootloadManager implements BootloadManagerInterface, Container\SingletonInterface
{
    public function __construct(
        private readonly ScopeInterface $scope,
        protected readonly InitializerInterface $initializer
    ) {
    }

    public function getClasses(): array
    {
        return $this->initializer->getRegistry()->getClasses();
    }

    public function bootload(array $classes, array $bootingCallbacks = [], array $bootedCallbacks = []): void
    {
        $this->scope->runScope(
            [self::class => $this],
            function () use ($classes, $bootingCallbacks, $bootedCallbacks): void {
                $this->boot($classes, $bootingCallbacks, $bootedCallbacks);
            }
        );
    }

    /**
     * Bootload all given bootloaders.
     *
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     */
    abstract protected function boot(array $classes, array $bootingCallbacks, array $bootedCallbacks): void;
}
