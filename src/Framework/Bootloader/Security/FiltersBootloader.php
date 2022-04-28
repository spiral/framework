<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Core\InterceptableCore;
use Spiral\Filter\InputScope;
use Spiral\Filters\Filter;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\FilterProvider;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Interceptors\AuthorizeFilterInterceptor;
use Spiral\Filters\Interceptors\Core;
use Spiral\Filters\Interceptors\ValidateFilterInterceptor;

final class FiltersBootloader extends Bootloader implements Container\InjectorInterface, Container\SingletonInterface
{
    protected const SINGLETONS = [
        FilterProviderInterface::class => [self::class, 'initFilterProvider'],
        InputInterface::class => InputScope::class,
    ];

    public function __construct(
        private readonly Container $container
    ) {
    }

    /**
     * Declare Filter injection.
     */
    public function boot(): void
    {
        $this->container->bindInjector(FilterInterface::class, self::class);
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

    private function initFilterProvider(Container $container)
    {
        $core = new InterceptableCore(new Core());

        $core->addInterceptor(new ValidateFilterInterceptor($this->container));
        $core->addInterceptor(new AuthorizeFilterInterceptor($this->container));

        return new FilterProvider($container, $core);
    }
}
