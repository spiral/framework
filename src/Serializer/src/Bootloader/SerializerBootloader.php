<?php

declare(strict_types=1);

namespace Spiral\Serializer\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Serializer\Config\SerializerConfig;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\Serializer\ProtoSerializer;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistryInterface;
use Google\Protobuf\Internal\Message;

final class SerializerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        SerializerManager::class => [self::class, 'initSerializerManager'],
        SerializerRegistryInterface::class => [self::class, 'initSerializerRegistry'],
        SerializerInterface::class => SerializerManager::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $factory
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $this->initConfig($env);
    }

    private function initSerializerManager(
        SerializerRegistryInterface $serializers,
        SerializerConfig $config
    ): SerializerManager {
        return new SerializerManager($serializers, $config->getDefault());
    }

    private function initSerializerRegistry(SerializerConfig $config): SerializerRegistryInterface
    {
        return new SerializerRegistry(\array_map([$this, 'wire'], $config->getSerializers()));
    }

    private function initConfig(EnvironmentInterface $env): void
    {
        $serializers = [
            'json' => new JsonSerializer(),
            'serializer' => new PhpSerializer(),
        ];
        if (\class_exists(Message::class)) {
            $serializers['proto'] = new ProtoSerializer();
        }

        $this->config->setDefaults(SerializerConfig::CONFIG, [
            'default' => $env->get('DEFAULT_SERIALIZER_FORMAT', SerializerConfig::DEFAULT_SERIALIZER),
            'serializers' => $serializers,
        ]);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function wire(string|Autowire|SerializerInterface $serializer): SerializerInterface
    {
        return match (true) {
            $serializer instanceof SerializerInterface => $serializer,
            $serializer instanceof Autowire => $serializer->resolve($this->factory),
            default => $this->container->get($serializer)
        };
    }
}
