<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Debug\Exception\StateException;
use Spiral\Debug\State;
use Spiral\Debug\StateCollector\EnvironmentCollector;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;

final class DebugBootloader extends Bootloader implements SingletonInterface
{
    protected const SINGLETONS = [
        EnvironmentCollector::class => EnvironmentCollector::class,
    ];

    protected const BINDINGS = [
        StateInterface::class => [self::class, 'state'],
    ];

    /** @var array<int, StateCollectorInterface|string> */
    private array $collectors = [];

    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    /**
     * Boot default state collector.
     */
    public function init(): void
    {
        $this->addStateCollector(EnvironmentCollector::class);
    }

    /**
     * @psalm-param class-string<StateCollectorInterface>|StateCollectorInterface $collector
     */
    public function addStateCollector(string|StateCollectorInterface $collector): void
    {
        $this->collectors[] = $collector;
    }

    /**
     * Create state and populate it with collectors.
     */
    private function state(): StateInterface
    {
        $state = new State();

        foreach ($this->collectors as $collector) {
            $collector = match (true) {
                \is_string($collector) => $this->factory->make($collector),
                $collector instanceof Autowire => $collector->resolve($this->factory),
                default => $collector,
            };

            if (!$collector instanceof StateCollectorInterface) {
                throw new StateException(
                    \sprintf(
                        'Unable to populate state, invalid state collector %s',
                        \is_object($collector) ? $collector::class : \gettype($collector)
                    )
                );
            }

            $collector->populate($state);
        }

        return $state;
    }
}
