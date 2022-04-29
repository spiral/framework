<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Filter\InputScope;
use Spiral\Filters\Filter;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\FilterProvider;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;
use Spiral\Validation\Bootloader\ValidationBootloader;

final class FiltersBootloader extends Bootloader implements Container\InjectorInterface, Container\SingletonInterface
{
    protected const DEPENDENCIES = [
        ValidationBootloader::class,
    ];

    protected const SINGLETONS = [
        FilterProviderInterface::class => FilterProvider::class,
        InputInterface::class          => InputScope::class,
    ];

    public function __construct(
        private readonly Container $container
    ) {
    }

    /**
     * Declare Filter injection.
     */
    public function init(): void
    {
        $this->container->bindInjector(Filter::class, self::class);
    }

    /**
     * @throws \Throwable
     */
    public function createInjection(\ReflectionClass $class, string $context = null): FilterInterface
    {
        return $this->container->get(FilterProviderInterface::class)->createFilter(
            $class->getName(),
            $this->container->get(InputInterface::class)
        );
    }
}
