<?php

declare(strict_types=1);

namespace Spiral\Interceptors;

/**
 * Helps to build a pipeline of interceptors.
 */
interface PipelineBuilderInterface
{
    /**
     * @param InterceptorInterface ...$interceptors List of interceptors to append to the pipeline.
     * @psalm-immutable
     */
    public function withInterceptors(InterceptorInterface ...$interceptors): static;

    public function build(HandlerInterface $last): HandlerInterface;
}
