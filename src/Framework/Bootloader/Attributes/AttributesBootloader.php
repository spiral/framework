<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Attributes;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\Reader as DoctrineReaderInterface;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Composite\SelectiveReader;
use Spiral\Attributes\Exception\InitializationException;
use Spiral\Attributes\Internal\Instantiator\Facade;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Attributes\Internal\Instantiator\NamedArgumentsInstantiator;
use Spiral\Attributes\Psr16CachedReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Config\ConfiguratorInterface;

class AttributesBootloader extends Bootloader
{
    protected const SINGLETONS = [
        ReaderInterface::class => [self::class, 'initReader'],
        InstantiatorInterface::class => [self::class, 'initInstantiator'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            AttributesConfig::CONFIG,
            [
                'annotations' => [
                    'support' => $env->get('SUPPORT_ANNOTATIONS', \interface_exists(DoctrineReaderInterface::class)),
                ],
                'cache' => [
                    'storage' => $env->get('ATTRIBUTES_CACHE_STORAGE', null),
                    'enabled' => $env->get('ATTRIBUTES_CACHE_ENABLED', false),
                ],
            ],
        );
    }

    private function initInstantiator(AttributesConfig $config): InstantiatorInterface
    {
        if ($config->isAnnotationsReaderEnabled()) {
            return new Facade();
        }

        /** @psalm-suppress InternalClass */
        return new NamedArgumentsInstantiator();
    }

    private function initReader(
        ContainerInterface $container,
        InstantiatorInterface $instantiator,
        AttributesConfig $config,
    ): ReaderInterface {
        $reader = new AttributeReader($instantiator);

        if ($config->isCacheEnabled()) {
            $provider = $container->get(CacheStorageProviderInterface::class);
            \assert($provider instanceof CacheStorageProviderInterface);

            $reader = new Psr16CachedReader($reader, $provider->storage($config->getCacheStorage()));
        }

        $supportAnnotations = $config->isAnnotationsReaderEnabled();

        if ($supportAnnotations) {
            if (!\interface_exists(DoctrineReaderInterface::class)) {
                throw new InitializationException(
                    'Doctrine annotations reader is not available, please install "doctrine/annotations" package',
                );
            }

            $reader = new SelectiveReader([
                $reader,
                new AnnotationReader(new DoctrineAnnotationReader()),
            ]);
        }

        return $reader;
    }
}
