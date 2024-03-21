<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * The domain core with a set of domain action interceptors (business logic middleware).
 */
final class InterceptableCore implements CoreInterface
{
    private InterceptorPipeline $pipeline;

    public function __construct(
        private readonly CoreInterface $core,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->pipeline = new InterceptorPipeline($dispatcher);
    }

    public function addInterceptor(CoreInterceptorInterface $interceptor): void
    {
        $this->pipeline->addInterceptor($interceptor);
    }

    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        return $this->pipeline->withCore($this->core)->callAction($controller, $action, $parameters);
    }
}
