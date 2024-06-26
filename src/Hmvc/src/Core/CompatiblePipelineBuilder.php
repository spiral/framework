<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Interceptors\PipelineBuilderInterface;

/**
 * Accepts {@see InterceptorInterface} and {@see CoreInterface} instances to build a pipeline.
 *
 * @deprecated Use {@see PipelineBuilder} instead.
 */
class CompatiblePipelineBuilder implements PipelineBuilderInterface
{
    private InterceptorPipeline $pipeline;

    public function __construct(?EventDispatcherInterface $dispatcher = null)
    {
        $this->pipeline = new InterceptorPipeline($dispatcher);
    }

    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }

    public function withInterceptors(CoreInterceptorInterface|InterceptorInterface ...$interceptors): static
    {
        $clone = clone $this;
        foreach ($interceptors as $interceptor) {
            $clone->pipeline->addInterceptor($interceptor);
        }

        return $clone;
    }

    public function build(HandlerInterface|CoreInterface $last): InterceptorPipeline
    {
        /** @psalm-suppress InvalidArgument */
        return $last instanceof HandlerInterface
            ? $this->pipeline->withHandler($last)
            : $this->pipeline->withCore($last);
    }
}
