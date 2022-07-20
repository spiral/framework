<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Domain;

use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrors;
use Spiral\Filters\RenderWith;

/**
 * Automatically validate all Filters and return array error in case of failure.
 */
class FilterInterceptor implements CoreInterceptorInterface
{
    /** @var ContainerInterface @internal */
    private $container;

    /** @var array @internal */
    private $cache = [];

    /** @var array<class-string<FilterInterface>, RenderErrors> @internal */
    private $renderersCache = [];

    /** @var ReaderInterface @internal */
    private $reader;

    /** @var RenderErrors @internal */
    private $renderErrors;

    /**
     * @param ContainerInterface $container
     * @param ReaderInterface $reader
     * @param null|RenderErrors  $renderErrors
     */
    public function __construct(ContainerInterface $container, ReaderInterface $reader, ?RenderErrors $renderErrors = null)
    {
        $this->container = $container;
        $this->reader = $reader;
        $this->renderErrors = $renderErrors ?: new DefaultFilterErrorsRenderer();
    }

    /**
     * @inheritDoc
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        foreach ($this->getDeclaredFilters($controller, $action) as $parameter => $filterClass) {
            if (isset($parameters[$parameter])) {
                continue;
            }

            /** @var FilterInterface $filter */
            $filter = $this->container->get($filterClass);

            if (isset($parameters['@context'])) {
                // other interceptors can define the validation contex
                $filter->setContext($parameters['@context']);
            }

            if (!$filter->isValid()) {
                return $this->renderInvalid($filter);
            }

            $parameters[$parameter] = $filter;
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * @param FilterInterface $filter
     * @return mixed
     *
     * @throws InvalidFilterException
     */
    protected function renderInvalid(FilterInterface $filter)
    {
        return ($this->renderersCache[get_class($filter)] ?? $this->renderErrors)->render($filter);
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array
     */
    private function getDeclaredFilters(string $controller, string $action): array
    {
        $key = sprintf('%s:%s', $controller, $action);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = [];
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            $class = $this->getParameterClass($parameter);

            if ($class === null) {
                continue;
            }

            if ($class->implementsInterface(FilterInterface::class)) {
                $this->cache[$key][$parameter->getName()] = $class->getName();

                if (null !== ($renderWith = $this->reader->firstClassMetadata($class, RenderWith::class))) {
                    $this->renderersCache[$class->getName()] = $this->container->get($renderWith->getRenderer());
                }
            }
        }

        return $this->cache[$key];
    }


    /**
     * @param \ReflectionParameter $parameter
     *
     * @return \ReflectionClass|null
     */
    private function getParameterClass(\ReflectionParameter $parameter): ?\ReflectionClass
    {
        $type = $parameter->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        if ($type->isBuiltin()) {
            return null;
        }

        return new \ReflectionClass($type->getName());
    }
}
