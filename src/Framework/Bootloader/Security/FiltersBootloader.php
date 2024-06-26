<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Security;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Core\CompatiblePipelineBuilder;
use Spiral\Core\Config\Proxy;
use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Interceptor\PopulateDataFromEntityInterceptor;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\Interceptor\ValidateFilterInterceptor;
use Spiral\Filters\Model\Mapper\EnumCaster;
use Spiral\Filters\Model\Mapper\CasterRegistry;
use Spiral\Filters\Model\Mapper\CasterRegistryInterface;
use Spiral\Filters\Model\Mapper\UuidCaster;
use Spiral\Framework\Spiral;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Request\InputManager;
use Spiral\Interceptors\PipelineBuilderInterface;

/**
 * @implements Container\InjectorInterface<FilterInterface>
 */
#[Singleton]
final class FiltersBootloader extends Bootloader implements Container\InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly BinderInterface $binder,
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function defineSingletons(): array
    {
        $this->binder
            ->getBinder(Spiral::HttpRequest)
            ->bindSingleton(
                InputInterface::class,
                static function (ContainerInterface $container, HttpConfig $config): InputScope {
                    return new InputScope(new InputManager($container, $config));
                }
            );

        $this->binder->bind(InputInterface::class, new Proxy(InputInterface::class, true));

        return [
            FilterProviderInterface::class => [self::class, 'initFilterProvider'],
            CasterRegistryInterface::class => [self::class, 'initCasterRegistry'],
        ];
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
        ?EventDispatcherInterface $dispatcher = null,
        ?PipelineBuilderInterface $builder = null,
    ): FilterProvider {
        $builder ??= new CompatiblePipelineBuilder($dispatcher);

        $list = [];
        foreach ($config->getInterceptors() as $interceptor) {
            $list[] = $container->get($interceptor);
        }

        $pipeline = $builder
            ->withInterceptors(...$list)
            ->build(new Core());

        return new FilterProvider($container, $container, $pipeline);
    }

    private function initCasterRegistry(): CasterRegistryInterface
    {
        return new CasterRegistry([new EnumCaster(), new UuidCaster()]);
    }
}
