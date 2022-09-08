<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\Event\InterceptorCalling;
use Spiral\Core\Exception\InterceptorException;

/**
 * Provides the ability to modify the call to the domain core on it's way to the action.
 */
final class InterceptorPipeline implements CoreInterface
{
    private ?CoreInterface $core = null;

    /** @var CoreInterceptorInterface[] */
    private array $interceptors = [];

    private int $position = 0;

    public function __construct(
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    public function addInterceptor(CoreInterceptorInterface $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    public function withCore(CoreInterface $core): self
    {
        $pipeline = clone $this;
        $pipeline->core = $core;

        return $pipeline;
    }

    /**
     * @throws \Throwable
     */
    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        if ($this->core === null) {
            throw new InterceptorException('Unable to invoke pipeline without assigned core');
        }

        $position = $this->position++;
        if (isset($this->interceptors[$position])) {
            $interceptor = $this->interceptors[$position];
            $this->dispatcher?->dispatch(new InterceptorCalling(
                controller: $controller,
                action: $action,
                parameters: $parameters,
                interceptor: $interceptor
            ));

            return $interceptor->process($controller, $action, $parameters, $this);
        }

        return $this->core->callAction($controller, $action, $parameters);
    }
}
