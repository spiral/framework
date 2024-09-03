<?php

declare(strict_types=1);

namespace Spiral\Interceptors;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Interceptors\Handler\InterceptorPipeline;

/**
 * Accepts only {@see InterceptorInterface} instances to build a pipeline.
 */
final class PipelineBuilder implements PipelineBuilderInterface
{
    private InterceptorPipeline $pipeline;

    public function __construct(
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->pipeline = new InterceptorPipeline($dispatcher);
    }

    public function withInterceptors(InterceptorInterface ...$interceptors): static
    {
        $clone = clone $this;
        $clone->pipeline = $this->pipeline->withInterceptors(...$interceptors);
        return $clone;
    }

    public function build(HandlerInterface $handler): HandlerInterface
    {
        return $this->pipeline->withHandler($handler);
    }
}
