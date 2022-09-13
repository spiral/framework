<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;

/**
 * @internal
 */
final class Container implements ContainerInterface
{
    use DestructorTrait;

    private State $state;
    private FactoryInterface|Factory $factory;

    public function __construct(Registry $constructor)
    {
        $constructor->set('container', $this);

        $this->state = $constructor->get('state', State::class);
        $this->factory = $constructor->get('factory', FactoryInterface::class);
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
     * @return T
     * @psalm-return ($id is class-string ? T : mixed)
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    public function get(string|Autowire $id, string $context = null): mixed
    {
        if ($id instanceof Autowire) {
            return $id->resolve($this->factory);
        }

        return $this->factory->make($id, [], $context);
    }

    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->state->bindings);
    }
}
