<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrorsInterface;

/**
 * Automatically validate all Filters and return array error in case of failure.
 */
class FilterInterceptor implements CoreInterceptorInterface
{
    public const STRATEGY_JSON_RESPONSE = 1;
    public const STRATEGY_EXCEPTION     = 2;

    /** @internal */
    protected ContainerInterface $container;

    protected int $strategy;

    /** @internal */
    protected RenderErrorsInterface $renderErrors;

    /** @internal */
    private array $cache = [];

    /**
     * @param RenderErrorsInterface|null $renderErrors Renderer for all filter errors.
     *        By default, will be used {@see DefaultFilterErrorsRendererInterface}
     */
    public function __construct(
        ContainerInterface $container,
        int $strategy = self::STRATEGY_JSON_RESPONSE,
        ?RenderErrorsInterface $renderErrors = null
    ) {
        $this->container = $container;
        $this->strategy = $strategy;
        $this->renderErrors = $renderErrors ?? new DefaultFilterErrorsRendererInterface($strategy);
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
                // other interceptors can define the validation context
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
     * @throws InvalidFilterException
     */
    protected function renderInvalid(FilterInterface $filter)
    {
        return $this->renderErrors->render($filter);
    }

    protected function buildCache(\ReflectionParameter $parameter, \ReflectionClass $class, string $key): void
    {
        $this->cache[$key][$parameter->getName()] = $class->getName();
    }

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
                $this->buildCache($parameter, $class, $key);
            }
        }

        return $this->cache[$key];
    }

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
