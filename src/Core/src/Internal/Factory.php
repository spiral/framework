<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Container\RecursiveProxyException;
use Spiral\Core\Exception\Scope\BadScopeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Factory\Ctx;

/**
 * @internal
 */
final class Factory implements FactoryInterface
{
    use DestructorTrait;

    private State $state;
    private Tracer $tracer;
    private Scope $scope;
    private Actor $actor;

    public function __construct(Registry $constructor)
    {
        $constructor->set('factory', $this);

        $this->state = $constructor->get('state', State::class);
        $this->tracer = $constructor->get('tracer', Tracer::class);
        $this->scope = $constructor->get('scope', Scope::class);
        $this->actor = $constructor->get('actor', Actor::class);
    }

    /**
     * @param \Stringable|string|null $context Related to parameter caused injection if any.
     *
     * @throws \Throwable
     */
    public function make(string $alias, array $parameters = [], \Stringable|string|null $context = null): mixed
    {
        if ($parameters === [] && \array_key_exists($alias, $this->state->singletons)) {
            return $this->state->singletons[$alias];
        }

        $binding = $this->state->bindings[$alias] ?? null;

        if ($binding === null) {
            return $this->resolveWithoutBinding($alias, $parameters, $context);
        }

        try {
            $this->tracer->push(
                false,
                action: 'resolve from binding',
                alias: $alias,
                scope: $this->scope->getScopeName(),
                context: $context,
                binding: $binding,
            );
            $this->tracer->push(true);

            unset($this->state->bindings[$alias]);
            return $this->actor->resolveBinding($binding, $alias, $context, $parameters);
        } finally {
            $this->state->bindings[$alias] ??= $binding;
            $this->tracer->pop(true);
            $this->tracer->pop(false);
        }
    }

    private function resolveWithoutBinding(
        string $alias,
        array $parameters = [],
        \Stringable|string|null $context = null,
    ): mixed {
        $parent = $this->scope->getParentFactory();

        if ($parent !== null) {
            try {
                $this->tracer->push(false, ...[
                    'current scope' => $this->scope->getScopeName(),
                    'jump to parent scope' => $this->scope->getParentScope()->getScopeName(),
                ]);
                /** @psalm-suppress TooManyArguments */
                return $parent->make($alias, $parameters, $context);
            } catch (BadScopeException $e) {
                if ($this->scope->getScopeName() !== $e->getScope()) {
                    throw $e;
                }
            } catch (ContainerExceptionInterface $e) {
                $className = match (true) {
                    $e instanceof RecursiveProxyException => throw $e,
                    $e instanceof NotFoundExceptionInterface => NotFoundException::class,
                    default => ContainerException::class,
                };
                throw new $className($this->tracer->combineTraceMessage(\sprintf(
                    'Can\'t resolve `%s`.',
                    $alias,
                )), previous: $e);
            } finally {
                $this->tracer->pop(false);
            }
        }

        $this->tracer->push(false, action: 'autowire', alias: $alias, context: $context);
        try {
            //No direct instructions how to construct class, make is automatically
            return $this->actor->autowire(
                new Ctx(alias: $alias, class: $alias, context: $context),
                $parameters,
            );
        } finally {
            $this->tracer->pop(false);
        }
    }
}
