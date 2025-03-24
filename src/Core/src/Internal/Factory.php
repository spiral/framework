<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

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


        $this->actor->resolveType($alias, $binding, $singleton, $injector, $actor, false);
        if ($parameters === [] && $singleton !== null) {
            return $singleton;
        }

        // Resolve without binding
        if ($binding === null) {
            $this->tracer->push(false, action: 'autowire', alias: $alias, context: $context);
            try {
                // No direct instructions how to construct class, make is automatically
                return $this->actor->autowire(
                    new Ctx(alias: $alias, class: $alias, context: $context, singleton: $parameters === [] ? null : false),
                    $parameters,
                    $actor,
                );
            } finally {
                $this->tracer->pop(false);
            }
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

            // unset($this->state->bindings[$alias]);
            return $actor->resolveBinding($binding, $alias, $context, $parameters);
        } finally {
            // $this->state->bindings[$alias] ??= $binding;
            $this->tracer->pop(true);
            $this->tracer->pop(false);
        }
    }
}
