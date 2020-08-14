<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\InterceptableCore;
use Spiral\Core\InterceptorPipeline;
use Spiral\Domain\Annotation\Pipeline;

class PipelineInterceptor implements CoreInterceptorInterface
{
    /** @var array  */
    private $cache = [];

    /** @var AnnotationReader  */
    private $reader;

    /** @var ContainerInterface  */
    private $container;

    /**
     * @param AnnotationReader   $reader
     * @param ContainerInterface $container
     */
    public function __construct(AnnotationReader $reader, ContainerInterface $container)
    {
        $this->reader = $reader;
        $this->container = $container;
    }

    /**
     * @param string        $controller
     * @param string        $action
     * @param array         $parameters
     * @param CoreInterface $core
     * @return mixed
     * @throws \Throwable
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        $annotation = $this->readAnnotation($controller, $action);
        if ($core instanceof InterceptorPipeline && $annotation->skipNext) {
            $this->cleanOriginalPipeline($core);
        }

        $pipeline = $this->getCachedPipeline($controller, $action, $annotation);
        if (!empty($pipeline)) {
            $core = $core instanceof InterceptorPipeline ? $core : new InterceptableCore($core);
            foreach ($pipeline as $interceptor) {
                $core->addInterceptor($interceptor);
            }
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * @param string $controller
     * @param string $action
     * @return Pipeline
     */
    private function readAnnotation(string $controller, string $action): Pipeline
    {
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return new Pipeline();
        }

        /** @var Pipeline $annotation */
        $annotation = $this->reader->getMethodAnnotation($method, Pipeline::class);
        return $annotation ?? new Pipeline();
    }

    /**
     * @param InterceptorPipeline $pipeline
     */
    private function cleanOriginalPipeline(InterceptorPipeline $pipeline): void
    {
        $pipelineReflection = new \ReflectionProperty(InterceptorPipeline::class, 'interceptors');
        $pipelineReflection->setAccessible(true);

        $oldInterceptors = $pipelineReflection->getValue($pipeline);
        $newInterceptors = [];
        foreach ($oldInterceptors as $interceptor) {
            $newInterceptors[] = $interceptor;
            if ($interceptor instanceof self) {
                break;
            }
        }

        if (count($newInterceptors) !== count($oldInterceptors)) {
            $pipelineReflection->setValue($pipeline, $newInterceptors);
        }
        $pipelineReflection->setAccessible(false);
    }

    /**
     * @param string   $controller
     * @param string   $action
     * @param Pipeline $annotation
     * @return array
     */
    private function getCachedPipeline(string $controller, string $action, Pipeline $annotation): array
    {
        $key = "{$controller}:{$action}";
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->extractAnnotationPipeline($annotation);
        }

        return $this->cache[$key];
    }

    /**
     * @param Pipeline $annotation
     * @return array
     */
    private function extractAnnotationPipeline(Pipeline $annotation): array
    {
        $interceptors = [];
        foreach ($annotation->pipeline as $interceptor) {
            try {
                $interceptors[] = $this->container->get($interceptor);
            } catch (\Throwable $e) {
            }
        }

        return $interceptors;
    }
}
