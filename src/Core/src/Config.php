<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;
use Spiral\Core\Internal\Binder;
use Spiral\Core\Internal\Factory;
use Spiral\Core\Internal\Invoker;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\State;
use Spiral\Core\Internal\Container;

class Config
{
    /** @var class-string<State> */
    public string $state = State::class;

    /** @var class-string<ResolverInterface> */
    public string $resolver = Resolver::class;

    /** @var class-string<FactoryInterface> */
    public string $factory = Factory::class;

    /** @var class-string<ContainerInterface> */
    public string $container = Container::class;

    /** @var class-string<BinderInterface> */
    public string $binder = Binder::class;

    /** @var class-string<InvokerInterface> */
    public string $invoker = Invoker::class;
}
