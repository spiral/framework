<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ResolverInterface;

/**
 * @internal
 */
final class Scope
{
    use DestructorTrait {
        destruct as private destructInternal;
    }

    private State $state;
    private BinderInterface $binder;
    private InvokerInterface $invoker;
    private ContainerInterface $container;
    private ResolverInterface $resolver;
    private Tracer $tracer;

    private ?string $name = null;

    private ?\Spiral\Core\Container $parent = null;
    private ?self $parentScope = null;

    public function __construct(Registry $constructor)
    {
        $constructor->set('scope', $this);

        $this->binder = $constructor->get('binder', BinderInterface::class);
        $this->state = $constructor->get('state', State::class);
        $this->invoker = $constructor->get('invoker', InvokerInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
        $this->tracer = $constructor->get('tracer', Tracer::class);
    }

    public function setUpScope(array $bindings, ?string $name = null)
    {
        $this->name = $name;
        // todo: more bindings from named scope?
        foreach ($bindings as $alias => $resolver) {
            $this->binder->bind($alias, $resolver);
        }
    }

    public function getScopeName(): ?string
    {
        return $this->name;
    }

    public function setParent(\Spiral\Core\Container $parent, self $parentScope): void
    {
        $this->parent = $parent;
        $this->parentScope = $parentScope;
    }

    public function getParent(): ?\Spiral\Core\Container
    {
        return $this->parent;
    }

    public function getParentScope(): ?self
    {
        return $this->parentScope;
    }

    public function destruct(): void
    {
        $this->parent = null;
        $this->parentScope = null;
        $this->destructInternal();
    }
}
