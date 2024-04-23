<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Handler;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Event\InterceptorCalling;
use Spiral\Interceptors\Exception\InterceptorException;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

/**
 * Interceptor pipeline.
 *
 * WARNING: make sure you don't use any legacy interceptors because they aren't supported with this pipeline.
 */
final class InterceptorPipeline implements HandlerInterface
{
    private ?HandlerInterface $handler = null;

    /** @var list<InterceptorInterface> */
    private array $interceptors = [];

    private int $position = 0;

    public function __construct(
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    public function addInterceptor(InterceptorInterface $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    public function withHandler(HandlerInterface $handler): self
    {
        $pipeline = clone $this;
        $pipeline->handler = $handler;
        return $pipeline;
    }

    /**
     * @throws \Throwable
     */
    public function handle(CallContext $context): mixed
    {
        if ($this->handler === null) {
            throw new InterceptorException('Unable to invoke pipeline without last handler.');
        }

        if (isset($this->interceptors[$this->position])) {
            $interceptor = $this->interceptors[$this->position];

            $this->dispatcher?->dispatch(new InterceptorCalling(context: $context, interceptor: $interceptor));

            return $interceptor->intercept($context, $this->next());
        }

        return $this->handler->handle($context);
    }

    private function next(): self
    {
        $pipeline = clone $this;
        ++$pipeline->position;
        return $pipeline;
    }
}
