<?php

declare(strict_types=1);

namespace Spiral\Core;

use IteratorAggregate;
use Psr\Container\ContainerInterface;
use Spiral\Core\Internal\Binder;
use Spiral\Core\Internal\Container;
use Spiral\Core\Internal\Factory;
use Spiral\Core\Internal\Invoker;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\Scope;
use Spiral\Core\Internal\State;
use Spiral\Core\Internal\Tracer;
use Traversable;

/**
 * Container configuration that will be used not only in the root container but also in all child containers.
 * The {@see self::$scopedBindings} property is internal and common for all containers.
 * By the reason you can access to bindings for any scope from any container.
 *
 * @implements IteratorAggregate<
 *     non-empty-string,
 *     class-string<State>|class-string<ResolverInterface>|class-string<FactoryInterface>|class-string<ContainerInterface>|class-string<BinderInterface>|class-string<InvokerInterface>|class-string<Tracer>|class-string<Scope>
 * >
 */
class Config implements IteratorAggregate
{
    /** @var class-string<Scope> */
    public readonly string $scope;
    public readonly Internal\Config\StateStorage $scopedBindings;
    private bool $rootLocked = true;

    /**
     * @param class-string<State> $state
     * @param class-string<ResolverInterface> $resolver
     * @param class-string<FactoryInterface> $factory
     * @param class-string<ContainerInterface> $container
     * @param class-string<BinderInterface> $binder
     * @param class-string<InvokerInterface> $invoker
     * @param class-string<Tracer> $tracer
     */
    public function __construct(
        public readonly string $state = State::class,
        public readonly string $resolver = Resolver::class,
        public readonly string $factory = Factory::class,
        public readonly string $container = Container::class,
        public readonly string $binder = Binder::class,
        public readonly string $invoker = Invoker::class,
        public readonly string $tracer = Tracer::class,
    ) {
        $this->scope = Scope::class;
        $this->scopedBindings = new Internal\Config\StateStorage();
    }

    public function getIterator(): Traversable
    {
        yield 'state' => $this->state;
        yield 'resolver' => $this->resolver;
        yield 'factory' => $this->factory;
        yield 'container' => $this->container;
        yield 'binder' => $this->binder;
        yield 'invoker' => $this->invoker;
        yield 'tracer' => $this->tracer;
        yield 'scope' => $this->scope;
    }

    /**
     * Mutex lock for root container.
     * First run of the method will return {@see true}, all subsequent calls will return {@see false}.
     * The parent container must call the method once and before any child container.
     */
    public function lockRoot(): bool
    {
        try {
            return $this->rootLocked;
        } finally {
            $this->rootLocked = false;
        }
    }
}
