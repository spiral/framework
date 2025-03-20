<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\Alias;
use Spiral\Core\Config\Binding;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\InvokerInterface;
use Spiral\Core\Options;
use Spiral\Core\ResolverInterface;
use Spiral\Core\ScopeInterface;

/**
 * @internal
 */
final class Hub
{
    use DestructorTrait;

    private State $state;
    private BinderInterface $binder;
    private InvokerInterface $invoker;
    private ContainerInterface $container;
    private ResolverInterface $resolver;
    private Tracer $tracer;
    private Scope $scope;
    private Options $options;

    public function __construct(Registry $constructor)
    {
        $constructor->set('hub', $this);

        $this->state = $constructor->get('state', State::class);
        $this->binder = $constructor->get('binder', BinderInterface::class);
        $this->invoker = $constructor->get('invoker', InvokerInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
        $this->tracer = $constructor->get('tracer', Tracer::class);
        $this->scope = $constructor->get('scope', Scope::class);
        $this->options = $constructor->getOptions();
    }

    /**
     * Get class name of the resolving object.
     * With it, you can quickly get cached singleton or detect that there are injector or binding.
     * The method does not detect that the class is instantiable.
     *
     * @param non-empty-string $alias
     * @return class-string|null Returns {@see null} if exactly one returning class cannot be resolved.
     * @psalm-suppress all
     */
    public function resolveType(
        string $alias,
        ?Binding &$binding = null,
        ?object $singleton = null,
        ?object $injector = null,
        ?ScopeInterface $scope = null,
    ): ?string {
        // Aliases to prevent circular dependencies
        $as = [];
        $static = $this;
        do {
            $bindings = &$static->state->bindings;
            $singletons = &$static->state->singletons;
            $injectors = &$static->state->injectors;
            $scope = $static->scope;
            if (\array_key_exists($alias, $singletons)) {
                $singleton = $singletons[$alias];
                $binding = $bindings[$alias] ?? null;
                $injector = $injectors[$alias] ?? null;
                return \is_object($singleton::class) ? $singleton::class : null;
            }

            if (\array_key_exists($alias, $bindings)) {
                $b = $bindings[$alias];
                if ($b::class === Alias::class) {
                    $alias = $b->alias;
                    if (\array_key_exists($alias, $as)) {
                        // A cycle detected
                        // todo Exception?
                        return null;
                    }
                    $as[$alias] = true;
                    continue;
                }

                $binding = $b;
                return $binding->getReturnClass();
            }

            if (\array_key_exists($alias, $injectors)) {
                $injector = $injectors[$alias];
                $binding = $bindings[$alias] ?? null;
                return $alias;
            }

            // Go to parent scope
            $static = $static->scope->getParentHub();
        } while ($static !== null);

        return \class_exists($alias) ? $alias : null;
    }
}
