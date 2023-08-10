<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Core\InterceptableCore;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Model\Factory\FilterFactory;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Interceptor\PopulateDataFromEntityInterceptor;
use Spiral\Filters\Model\Interceptor\Validation\ValidateFilterInterceptor;
use Spiral\Filters\Model\Interceptor\Validation\Core as ValidationCore;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\Mapper\Enum;
use Spiral\Filters\Model\Mapper\SetterRegistry;
use Spiral\Filters\Model\Mapper\SetterRegistryInterface;
use Spiral\Filters\Model\Mapper\Uuid;
use Spiral\Filters\Model\Schema\AttributeReader;
use Spiral\Filters\Model\Schema\DefaultReader;
use Spiral\Filters\Model\Schema\SchemaProvider;
use Spiral\Filters\Model\Schema\SchemaProviderInterface;

/**
 * @implements Container\InjectorInterface<FilterInterface>
 */
final class FiltersBootloader extends Bootloader implements Container\InjectorInterface, Container\SingletonInterface
{
    protected const SINGLETONS = [
        FilterProviderInterface::class => [self::class, 'initFilterProvider'],
        InputInterface::class => InputScope::class,
        FilterFactory::class => FilterFactory::class,
        SchemaProviderInterface::class => [self::class, 'initSchemaProvider'],
        SetterRegistryInterface::class => [self::class, 'initSetterRegistry'],
    ];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly BinderInterface $binder,
        private readonly ConfiguratorInterface $config
    ) {
    }

    /**
     * Declare Filter injection.
     */
    public function init(): void
    {
        $this->binder->bindInjector(FilterInterface::class, self::class);

        $this->config->setDefaults(
            FiltersConfig::CONFIG,
            [
                'interceptors' => [
                    PopulateDataFromEntityInterceptor::class,
                ],
                'validationInterceptors' => [
                    ValidateFilterInterceptor::class,
                ],
            ]
        );
    }

    /**
     * @param class-string<CoreInterceptorInterface>|string $interceptor
     */
    public function addInterceptor(string $interceptor): void
    {
        $this->config->modify(
            FiltersConfig::CONFIG,
            new Append('interceptors', null, $interceptor)
        );
    }

    /**
     * @throws \Throwable
     */
    public function createInjection(\ReflectionClass $class, string $context = null): FilterInterface
    {
        /** @var FilterBag $filter */
        return $this->container->get(FilterProviderInterface::class)->createFilter(
            $class->getName(),
            $this->container->get(InputInterface::class)
        );
    }

    private function initFilterProvider(
        Container $container,
        FiltersConfig $config,
        FilterFactory $factory,
        SchemaProviderInterface $schemaProvider,
        ?EventDispatcherInterface $dispatcher = null
    ): FilterProvider {
        $core = new InterceptableCore(new Core(), $dispatcher);
        foreach ($config->getInterceptors() as $interceptor) {
            $core->addInterceptor($container->get($interceptor));
        }

        $validationCode = new InterceptableCore(new ValidationCore($container), $dispatcher);
        foreach ($config->getValidationInterceptors() as $interceptor) {
            $validationCode->addInterceptor($container->get($interceptor));
        }

        return new FilterProvider($container, $core, $validationCode, $factory, $schemaProvider);
    }

    private function initSchemaProvider(FactoryInterface $factory): SchemaProviderInterface
    {
        return $factory->make(SchemaProvider::class, [
            'readers' => [
                $factory->make(AttributeReader::class),
                $factory->make(DefaultReader::class),
            ],
        ]);
    }

    private function initSetterRegistry(): SetterRegistryInterface
    {
        return new SetterRegistry([new Enum(), new Uuid()]);
    }
}
