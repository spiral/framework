<?php

declare(strict_types=1);

namespace Framework\Debug;

use Spiral\Boot\DispatcherInterface;
use Spiral\Bootloader\DebugBootloader;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\Debug\State;
use Spiral\Debug\StateCollector\EnvironmentCollector;
use Spiral\Testing\TestCase;

final class EnvironmentCollectorTest extends TestCase
{
    public function defineBootloaders(): array
    {
        return [
            DebugBootloader::class,
        ];
    }

    public function testDefinition(): void
    {
        $c = $this->getContainer();
        $dispatcher = $this->createMock(DispatcherInterface::class);

        /** @var State $state */
        $state = $c->runScope(
            new Scope(name: 'foo', bindings: [DispatcherInterface::class => $dispatcher], checkpoint: true),
            function (ScopeInterface $scope) use ($c) {
                return $scope->runScope(new Scope(name: 'foo-bar'), function (FactoryInterface $factory) {
                    $collector = $factory->make(EnvironmentCollector::class);
                    $state = new State();
                    $collector->populate($state);
                    return $state;
                });
            },
        );

        self::assertArrayHasKey('dispatcher', $state->getTags());
        self::assertArrayHasKey('environment', $state->getVariables());
    }
}
