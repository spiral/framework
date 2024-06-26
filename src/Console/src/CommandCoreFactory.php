<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Interceptor\AttributeInterceptor;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\CompatiblePipelineBuilder;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Interceptors\PipelineBuilderInterface;

#[Scope('console.command')]
final class CommandCoreFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @param array<class-string<CoreInterceptorInterface|InterceptorInterface>> $interceptors
     */
    public function make(
        array $interceptors,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?PipelineBuilderInterface $pipelineBuilder = null,
    ): CoreInterface|HandlerInterface {
        /** @var CommandCore $core */
        $core = $this->container->get(CommandCore::class);
        $pipelineBuilder ??= new CompatiblePipelineBuilder($eventDispatcher);

        $resolved = [];
        foreach ($interceptors as $interceptor) {
            $resolved[] = $this->container->get($interceptor);
        }

        $resolved[] = $this->container->get(AttributeInterceptor::class);

        return $pipelineBuilder
            ->withInterceptors(...$resolved)
            ->build($core);
    }
}
