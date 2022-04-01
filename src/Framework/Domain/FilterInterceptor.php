<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;

/**
 * Automatically validate all Filters and return array error in case of failure.
 */
class FilterInterceptor implements CoreInterceptorInterface
{
    public const STRATEGY_JSON_RESPONSE = 1;
    public const STRATEGY_EXCEPTION     = 2;

    /** @internal */
    private array $cache = [];

    public function __construct(
        /** @internal */
        private ContainerInterface $container,
        private int $strategy = self::STRATEGY_JSON_RESPONSE
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
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
     * @throws InvalidFilterException
     */
    protected function renderInvalid(FilterInterface $filter): array
    {
        switch ($this->strategy) {
            case self::STRATEGY_JSON_RESPONSE:
                return [
                    'status' => 400,
                    'errors' => $filter->getErrors(),
                ];
            default:
                throw new InvalidFilterException($filter);
        }
    }

    private function getDeclaredFilters(string $controller, string $action): array
    {
        $key = \sprintf('%s:%s', $controller, $action);
        if (\array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = [];
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            $class = $this->getParameterClass($parameter);

            if ($class === null) {
                continue;
            }

            if ($class->implementsInterface(FilterInterface::class)) {
                $this->cache[$key][$parameter->getName()] = $class->getName();
            }
        }

        return $this->cache[$key];
    }

    private function getParameterClass(\ReflectionParameter $parameter): ?\ReflectionClass
    {
        $type = $parameter->getType();

        return match (true) {
            !$type instanceof \ReflectionNamedType => null,
            $type->isBuiltin() => null,
            default => new \ReflectionClass($type->getName())
        };
    }
}
