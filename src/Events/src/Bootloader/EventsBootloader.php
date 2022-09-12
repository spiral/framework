<?php

declare(strict_types=1);

namespace Spiral\Events\Bootloader;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\EventDispatcherAwareInterface;
use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerProcessorRegistry;
use Spiral\Events\Processor\AttributeProcessor;
use Spiral\Events\Processor\ConfigProcessor;
use Spiral\Events\Processor\ProcessorInterface;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

final class EventsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        TokenizerListenerBootloader::class,
        AttributesBootloader::class,
    ];

    protected const SINGLETONS = [
        ListenerFactoryInterface::class => AutowireListenerFactory::class,
        ListenerProcessorRegistry::class => ListenerProcessorRegistry::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $configs
    ) {
    }

    public function init(): void
    {
        $this->configs->setDefaults(EventsConfig::CONFIG, [
            'listeners' => [],
            'processors' => [
                AttributeProcessor::class,
                ConfigProcessor::class,
            ],
        ]);
    }

    public function boot(
        ContainerInterface $container,
        FactoryInterface $factory,
        EventsConfig $config,
        AbstractKernel $kernel,
        ListenerProcessorRegistry $registry,
        FinalizerInterface $finalizer,
        ?EventDispatcherInterface $eventDispatcher = null
    ): void {
        foreach ($config->getProcessors() as $processor) {
            if (\is_string($processor)) {
                $processor = $container->get($processor);
            } elseif ($processor instanceof Container\Autowire) {
                $processor = $processor->resolve($factory);
            }

            \assert($processor instanceof ProcessorInterface);

            $registry->addProcessor($processor);
        }

        $kernel->bootstrapped(static function () use ($registry): void {
            $registry->process();
        });

        if ($finalizer instanceof EventDispatcherAwareInterface && $eventDispatcher !== null) {
            $finalizer->setEventDispatcher($eventDispatcher);
        }
    }
}
