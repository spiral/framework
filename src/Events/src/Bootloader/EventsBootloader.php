<?php

declare(strict_types=1);

namespace Spiral\Events\Bootloader;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\CompatiblePipelineBuilder;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\EventDispatcher;
use Spiral\Events\EventDispatcherAwareInterface;
use Spiral\Events\Interceptor\Core;
use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerProcessorRegistry;
use Spiral\Events\Processor\AttributeProcessor;
use Spiral\Events\Processor\ConfigProcessor;
use Spiral\Events\Processor\ProcessorInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

/**
 * @psalm-import-type TInterceptor from EventsConfig
 */
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
            'interceptors' => [],
        ]);
    }

    public function boot(
        Container $container,
        FactoryInterface $factory,
        EventsConfig $config,
        AbstractKernel $kernel,
        ListenerProcessorRegistry $registry,
        FinalizerInterface $finalizer,
        ?EventDispatcherInterface $eventDispatcher = null
    ): void {
        if ($eventDispatcher !== null) {
            $this->initEventDispatcher(new Core($eventDispatcher), $config, $container, $factory);
        }

        foreach ($config->getProcessors() as $processor) {
            $processor = $this->autowire($processor, $container, $factory);

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

    /**
     * @param TInterceptor $interceptor
     */
    public function addInterceptor(
        string|InterceptorInterface|CoreInterceptorInterface|Container\Autowire $interceptor,
    ): void {
        $this->configs->modify(EventsConfig::CONFIG, new Append('interceptors', null, $interceptor));
    }

    private function initEventDispatcher(
        Core $core,
        EventsConfig $config,
        Container $container,
        FactoryInterface $factory
    ): void {
        $builder = new CompatiblePipelineBuilder();
        $list = [];
        foreach ($config->getInterceptors() as $interceptor) {
            $list[] = $this->autowire($interceptor, $container, $factory);
        }

        $pipeline = $builder->withInterceptors(...$list)->build($core);
        $container->removeBinding(EventDispatcherInterface::class);
        $container->bindSingleton(EventDispatcherInterface::class, new EventDispatcher($pipeline));
    }

    /**
     * @template T of object
     *
     * @param class-string<T>|Autowire<T>|T $id
     *
     * @return T
     *
     * @throws ContainerExceptionInterface
     */
    private function autowire(string|object $id, ContainerInterface $container, FactoryInterface $factory): object
    {
        return match (true) {
            \is_string($id) => $container->get($id),
            $id instanceof Autowire => $id->resolve($factory),
            default => $id,
        };
    }
}
