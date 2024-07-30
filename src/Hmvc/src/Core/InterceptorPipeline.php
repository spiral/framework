<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\Event\InterceptorCalling;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

/**
 * Provides the ability to modify the call to the domain core on it's way to the action.
 *
 * @deprecated use {@see \Spiral\Interceptors\Handler\InterceptorPipeline} instead
 */
final class InterceptorPipeline implements CoreInterface, HandlerInterface
{
    private ?CoreInterface $core = null;
    private ?HandlerInterface $handler = null;

    /** @var list<CoreInterceptorInterface|InterceptorInterface> */
    private array $interceptors = [];

    private int $position = 0;
    private ?CallContextInterface $context = null;

    public function __construct(
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    public function addInterceptor(CoreInterceptorInterface|InterceptorInterface $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    /**
     * @psalm-immutable
     */
    public function withCore(CoreInterface $core): self
    {
        $pipeline = clone $this;
        $pipeline->core = $core;
        $pipeline->handler = null;
        return $pipeline;
    }

    /**
     * @psalm-immutable
     */
    public function withHandler(HandlerInterface $handler): self
    {
        $pipeline = clone $this;
        $pipeline->handler = $handler;
        $pipeline->core = null;
        return $pipeline;
    }

    /**
     * @throws \Throwable
     */
    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        if ($this->context === null) {
            return $this->handle(
                new CallContext(Target::fromPathArray([$controller, $action]), $parameters),
            );
        }

        if ($this->context->getTarget()->getPath() === [$controller, $action]) {
            return $this->handle($this->context->withArguments($parameters));
        }

        return $this->handle(
            $this->context->withTarget(
                Target::fromPathArray([$controller, $action]),
            )->withArguments($parameters)
        );
    }

    /**
     * @throws \Throwable
     */
    public function handle(CallContextInterface $context): mixed
    {
        if ($this->core === null && $this->handler === null) {
            throw new InterceptorException('Unable to invoke pipeline without last handler.');
        }

        $path = $context->getTarget()->getPath();

        if (isset($this->interceptors[$this->position])) {
            $interceptor = $this->interceptors[$this->position];
            $handler = $this->nextWithContext($context);

            $this->dispatcher?->dispatch(
                new InterceptorCalling(
                    controller: $path[0] ?? '',
                    action: $path[1] ?? '',
                    parameters: $context->getArguments(),
                    interceptor: $interceptor,
                )
            );

            return $interceptor instanceof CoreInterceptorInterface
                ? $interceptor->process($path[0] ?? '', $path[1] ?? '', $context->getArguments(), $handler)
                : $interceptor->intercept($context, $handler);
        }

        return $this->core === null
            ? $this->handler->handle($context)
            : $this->core->callAction($path[0] ?? '', $path[1] ?? '', $context->getArguments());
    }

    private function nextWithContext(CallContextInterface $context): self
    {
        $pipeline = clone $this;
        $pipeline->context = $context;
        ++$pipeline->position;
        return $pipeline;
    }
}
