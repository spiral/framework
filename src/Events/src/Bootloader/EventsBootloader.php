<?php

declare(strict_types=1);

namespace Spiral\Events\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\ListenerLocator;
use Spiral\Events\ListenerLocatorInterface;
use Spiral\Events\Processor\AttributeProcessor;
use Spiral\Events\Processor\ConfigProcessor;
use Spiral\Events\Processor\ProcessorInterface;

final class EventsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AttributesBootloader::class,
    ];

    protected const BINDINGS = [
        ListenerLocatorInterface::class => ListenerLocator::class,
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

    public function boot(EventsConfig $config, ContainerInterface $container): void
    {
        foreach ($config->getProcessors() as $processor) {
            if (!$processor instanceof ProcessorInterface) {
                $processor = $container->get($processor);
                $processor->process();
            }
        }
    }
}
