<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\InterceptableCore;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\FilterProvider;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Interceptors\AuthorizeFilterInterceptor;
use Spiral\Filters\Interceptors\Core;
use Spiral\Filters\Interceptors\PopulateDataFromEntityInterceptor;
use Spiral\Filters\Interceptors\ValidateFilterInterceptor;

final class FiltersBootloader extends Bootloader implements Container\InjectorInterface, Container\SingletonInterface
{
    protected const SINGLETONS = [
        FilterProviderInterface::class => [self::class, 'initFilterProvider'],
        InputInterface::class => InputScope::class,
    ];

    public function __construct(
        private readonly Container $container,
        private readonly ConfiguratorInterface $config
    ) {
    }

    /**
     * Declare Filter injection.
     */
    public function init(): void
    {
        $this->container->bindInjector(FilterInterface::class, self::class);

        $this->config->setDefaults(
            FiltersConfig::CONFIG,
            [
                'interceptors' => [
                    PopulateDataFromEntityInterceptor::class,
                    ValidateFilterInterceptor::class,
                    AuthorizeFilterInterceptor::class,
                ],
            ]
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

    private function initFilterProvider(Container $container, FiltersConfig $config)
    {
        $core = new InterceptableCore(new Core());

        foreach ($config->getInterceptors() as $interceptor) {
            $core->addInterceptor($container->get($interceptor));
        }

        return new FilterProvider($container, $core);
    }
}
