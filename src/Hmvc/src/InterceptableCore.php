<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

/**
 * The domain core with a set of domain action interceptors (business logic middleware).
 *
 * @deprecated use {@see \Spiral\Interceptors\Handler\InterceptorPipeline} instead
 */
final class InterceptableCore implements CoreInterface, HandlerInterface
{
    private readonly InterceptorPipeline $pipeline;

    public function __construct(
        private readonly CoreInterface $core,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->pipeline = new InterceptorPipeline($dispatcher);
    }

    public function addInterceptor(CoreInterceptorInterface|InterceptorInterface $interceptor): void
    {
        $this->pipeline->addInterceptor($interceptor);
    }

    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        return $this->pipeline->withCore($this->core)->callAction($controller, $action, $parameters);
    }

    public function handle(CallContextInterface $context): mixed
    {
        return $this->pipeline->withCore($this->core)->handle($context);
    }
}
