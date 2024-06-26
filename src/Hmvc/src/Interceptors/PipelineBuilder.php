<?php

declare(strict_types=1);

namespace Spiral\Interceptors;

use Spiral\Interceptors\Handler\InterceptorPipeline;

/**
 * Accepts only {@see InterceptorInterface} instances to build a pipeline.
 */
class PipelineBuilder implements PipelineBuilderInterface
{
    private InterceptorPipeline $pipeline;

    public function __construct()
    {
        $this->pipeline = new InterceptorPipeline();
    }

    public function withInterceptors(InterceptorInterface ...$interceptors): static
    {
        $clone = clone $this;
        $clone->pipeline = $this->pipeline->withInterceptors(...$interceptors);
        return $clone;
    }

    public function build(HandlerInterface $last): HandlerInterface
    {
        return $this->pipeline->withHandler($last);
    }
}
