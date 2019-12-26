<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
        EnvironmentCollector::class => EnvironmentCollector::class
    ];

    protected const BINDINGS = [
        StateInterface::class => [self::class, 'state']
    ];

    /** @var StateCollectorInterface[]|string[] */
    private $collectors = [];

    /** @var FactoryInterface */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Boot default state collector.
     */
    public function boot(): void
    {
        $this->addStateCollector(EnvironmentCollector::class);
    }

    /**
     * @param string|StateCollectorInterface $collector
     */
    public function addStateCollector($collector): void
    {
        $this->collectors[] = $collector;
    }

    /**
     * Create state and populate it with collectors.
     *
     * @return StateInterface
     */
    private function state(): StateInterface
    {
        $state = new State();

        foreach ($this->collectors as $collector) {
            if (is_string($collector)) {
                $collector = $this->factory->make($collector);
            }

            if ($collector instanceof Autowire) {
                $collector = $collector->resolve($this->factory);
            }

            if (!$collector instanceof StateCollectorInterface) {
                throw new StateException(
                    sprintf(
                        'Unable to populate state, invalid state collector %s',
                        is_object($collector) ? get_class($collector) : gettype($collector)
                    )
                );
            }

            $collector->populate($state);
        }

        return $state;
    }
}
