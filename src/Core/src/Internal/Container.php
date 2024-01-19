<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;

/**
 * @internal
 */
final class Container implements ContainerInterface
{
    use DestructorTrait;

    private State $state;
    private FactoryInterface|Factory $factory;
    private Scope $scope;

    public function __construct(Registry $constructor)
    {
        $constructor->set('container', $this);

        $this->state = $constructor->get('state', State::class);
        $this->factory = $constructor->get('factory', FactoryInterface::class);
        $this->scope = $constructor->get('scope', Scope::class);
    }

    /**
     * Context parameter will be passed to class injectors, which makes possible to use this method
     * as:
     *
     * $this->container->get(DatabaseInterface::class, 'default');
     *
     * Attention, context ignored when outer container has instance by alias.
     *
     * @template T
     *
     * @param class-string<T>|string|Autowire $id
     * @param string|null $context Call context.
     *
     * @return ($id is class-string ? T : mixed)
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    public function get(string|Autowire $id, \Stringable|string|null $context = null): mixed
    {
        if ($id instanceof Autowire) {
            return $id->resolve($this->factory);
        }

        /** @psalm-suppress TooManyArguments */
        return $this->factory->make($id, [], $context);
    }

    public function has(string $id): bool
    {
        if (\array_key_exists($id, $this->state->bindings) || \array_key_exists($id, $this->state->singletons)) {
            return true;
        }

        $parent = $this->scope->getParent();

        return $parent !== null && $parent->has($id);
    }
}
