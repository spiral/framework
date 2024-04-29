<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\InterceptableCore;
use Spiral\Core\InterceptorPipeline;
use Spiral\Domain\Annotation\Pipeline;

/**
 * @deprecated Will be removed in future releases.
 */
class PipelineInterceptor implements CoreInterceptorInterface
{
    private array $cache = [];

    public function __construct(
        private ReaderInterface $reader,
        private ContainerInterface $container,
        private ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $annotation = $this->readAnnotation($controller, $action);
        if ($core instanceof InterceptorPipeline && $annotation->skipNext) {
            $this->removeNextInterceptorsFromOriginalPipeline($core);
        }

        $pipeline = $this->getCachedPipeline($controller, $action, $annotation);
        if (!empty($pipeline)) {
            if ($core instanceof InterceptorPipeline) {
                $this->injectInterceptorsIntoOriginalPipeline($core, $pipeline);
            } else {
                $core = new InterceptableCore($core, $this->dispatcher);
                foreach ($pipeline as $interceptor) {
                    $core->addInterceptor($interceptor);
                }
            }
        }

        return $core->callAction($controller, $action, $parameters);
    }

    private function readAnnotation(string $controller, string $action): Pipeline
    {
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException) {
            return new Pipeline();
        }

        /** @var Pipeline $annotation */
        $annotation = $this->reader->firstFunctionMetadata($method, Pipeline::class);
        return $annotation ?? new Pipeline();
    }

    private function removeNextInterceptorsFromOriginalPipeline(InterceptorPipeline $pipeline): void
    {
        $pipelineReflection = new \ReflectionProperty(InterceptorPipeline::class, 'interceptors');

        $oldInterceptors = $pipelineReflection->getValue($pipeline);
        $newInterceptors = [];
        foreach ($oldInterceptors as $interceptor) {
            $newInterceptors[] = $interceptor;
            if ($interceptor instanceof self) {
                break;
            }
        }

        if (\count($newInterceptors) !== \count($oldInterceptors)) {
            $pipelineReflection->setValue($pipeline, $newInterceptors);
        }
    }

    private function injectInterceptorsIntoOriginalPipeline(InterceptorPipeline $pipeline, array $interceptors): void
    {
        $pipelineReflection = new \ReflectionProperty(InterceptorPipeline::class, 'interceptors');

        $oldInterceptors = $pipelineReflection->getValue($pipeline);
        $newInterceptors = [];
        foreach ($oldInterceptors as $interceptor) {
            $newInterceptors[] = $interceptor;
            if ($interceptor instanceof self) {
                foreach ($interceptors as $newInterceptor) {
                    $newInterceptors[] = $newInterceptor;
                }
            }
        }

        if (\count($newInterceptors) !== \count($oldInterceptors)) {
            $pipelineReflection->setValue($pipeline, $newInterceptors);
        }
    }

    private function getCachedPipeline(string $controller, string $action, Pipeline $annotation): array
    {
        $key = "{$controller}:{$action}";
        if (!\array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->extractAnnotationPipeline($annotation);
        }

        return $this->cache[$key];
    }

    private function extractAnnotationPipeline(Pipeline $annotation): array
    {
        $interceptors = [];
        foreach ($annotation->pipeline as $interceptor) {
            try {
                $interceptors[] = $this->container->get($interceptor);
            } catch (\Throwable) {
            }
        }

        return $interceptors;
    }
}
