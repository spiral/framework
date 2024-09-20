<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Debug\Config\DebugConfig;
use Spiral\Debug\Exception\StateException;
use Spiral\Debug\State;
use Spiral\Debug\StateCollector\EnvironmentCollector;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;

/**
 * @psalm-import-type TCollector from DebugConfig
 * @psalm-import-type TTag from DebugConfig
 */
#[Singleton]
final class DebugBootloader extends Bootloader
{
    protected const SINGLETONS = [
        EnvironmentCollector::class => EnvironmentCollector::class,
    ];

    protected const BINDINGS = [
        StateInterface::class => [self::class, 'state'],
    ];

    public function __construct(
        private readonly InvokerInterface $invoker,
        private readonly ConfiguratorInterface $config,
    ) {
    }

    /**
     * Boot default state collector.
     */
    public function init(): void
    {
        $this->initDefaultConfig();
        $this->addStateCollector(EnvironmentCollector::class);
    }

    /**
     * @param non-empty-string $key
     * @param TTag $value
     */
    public function addTag(string $key, string|\Stringable|\Closure $value): void
    {
        $this->config->modify(DebugConfig::CONFIG, new Append('tags', $key, $value));
    }

    /**
     * @psalm-param TCollector $collector
     */
    public function addStateCollector(string|StateCollectorInterface|Autowire $collector): void
    {
        $this->config->modify(DebugConfig::CONFIG, new Append('collectors', null, $collector));
    }

    /**
     * Create state and populate it with collectors.
     */
    private function state(
        #[Proxy] FactoryInterface $factory,
        DebugConfig $config,
    ): StateInterface {
        $state = new State();

        foreach ($config->getTags() as $key => $value) {
            if ($value instanceof \Closure) {
                $value = $this->invoker->invoke($value);
            }

            if (!\is_string($value) && !$value instanceof \Stringable) {
                throw new StateException(
                    \sprintf(
                        'Invalid tag value, `string` expected got `%s`',
                        \is_object($value) ? $value::class : \gettype($value),
                    ),
                );
            }

            $state->setTag((string)$key, (string)$value);
        }

        $errors = [];

        foreach ($config->getCollectors() as $collector) {
            try {
                $collector = match (true) {
                    \is_string($collector) => $factory->make($collector),
                    $collector instanceof Autowire => $collector->resolve($factory),
                    default => $collector,
                };
            } catch (\Throwable) {
                $errors[] = \is_string($collector) || $collector instanceof \Stringable
                    ? (string) $collector
                    : \get_debug_type($collector);
                continue;
            }

            if (!$collector instanceof StateCollectorInterface) {
                throw new StateException(
                    \sprintf(
                        'Unable to populate state, invalid state collector %s',
                        \is_object($collector) ? $collector::class : \gettype($collector),
                    ),
                );
            }

            $collector->populate($state);
        }

        $errors === [] or $state->setTags(['unresolved-collectors' => \implode(', ', $errors)]);

        return $state;
    }

    private function initDefaultConfig(): void
    {
        $this->config->setDefaults(DebugConfig::CONFIG, [
            'collectors' => [],
            'tags' => [],
        ]);
    }
}
