<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Core\InterceptableCore;
use Spiral\Filter\InputScope;
use Spiral\Filters\Filter;
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
        FilterProviderInterface::class => FilterProvider::class,
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
        $this->container->bindInjector(Filter::class, self::class);
    }

    /**
     * @throws \Throwable
     */
    public function createInjection(\ReflectionClass $class, string $context = null): FilterInterface
    {
        $core = new InterceptableCore(new Core(
            $this->container->get(FilterProviderInterface::class),
            $this->container->get(InputInterface::class)
        ));

        $core->addInterceptor(new ValidateFilterInterceptor($this->container));
        $core->addInterceptor(new AuthorizeFilterInterceptor($this->container));

        return $core->callAction($class->getName(), 'handle', ['context' => $context]);
    }
}
