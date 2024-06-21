<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Interceptors\PipelineBuilderInterface;

/**
 * Accepts {@see InterceptorInterface} and {@see CoreInterface} instances to build a pipeline.
 */
class CompatiblePipelineBuilder implements PipelineBuilderInterface
{
    private InterceptorPipeline $pipeline;

    public function __construct()
    {
        $this->pipeline = new InterceptorPipeline();
    }

    public function withInterceptors(CoreInterceptorInterface|InterceptorInterface ...$interceptors): static
    {
        $clone = clone $this;
        foreach ($interceptors as $interceptor) {
            $clone->pipeline->addInterceptor($interceptor);
        }

        return $clone;
    }

    public function build(HandlerInterface|CoreInterface $last): HandlerInterface
    {
        /** @psalm-suppress InvalidArgument */
        return $last instanceof HandlerInterface
            ? $this->pipeline->withHandler($last)
            : $this->pipeline->withCore($last);
    }

    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }
}
