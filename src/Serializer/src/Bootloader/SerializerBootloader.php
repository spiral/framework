<?php

declare(strict_types=1);

namespace Spiral\Serializer\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Serializer\Config\SerializerConfig;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistryInterface;

final class SerializerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        SerializerManager::class => [self::class, 'initSerializerManager'],
        SerializerRegistryInterface::class => [self::class, 'initSerializerRegistry'],
        SerializerInterface::class => SerializerManager::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly Container $container
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $this->initConfig($env);
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initSerializerManager(
        SerializerRegistryInterface $serializers,
        SerializerConfig $config
    ): SerializerManager {
        return new SerializerManager($serializers, $config->getDefault());
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initSerializerRegistry(SerializerConfig $config): SerializerRegistryInterface
    {
        return new SerializerRegistry(\array_map([$this, 'wire'], $config->getSerializers()));
    }

    private function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(SerializerConfig::CONFIG, [
            'default' => $env->get('DEFAULT_SERIALIZER_FORMAT', SerializerConfig::DEFAULT_SERIALIZER),
            'serializers' => [
                'json' => new JsonSerializer(),
                'serializer' => new PhpSerializer(),
            ],
        ]);
    }

    private function wire(string|Autowire|SerializerInterface $serializer): SerializerInterface
    {
        return match (true) {
            $serializer instanceof SerializerInterface => $serializer,
            $serializer instanceof Autowire => $serializer->resolve($this->container),
            default => $this->container->get($serializer)
        };
    }
}
