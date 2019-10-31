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
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\FilterInterface;

/**
 * Automatically validate all Filters and return array error in case of failure.
 */
final class FilterInterceptor implements CoreInterceptorInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var array */
    private $filterCache = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

            if (!$filter->isValid()) {
                // add more options in future
                return [
                    'status' => 400,
                    'errors' => $filter->getErrors()
                ];
            }

            $parameters[$parameter] = $filter;
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array
     */
    private function getDeclaredFilters(string $controller, string $action): array
    {
        $key = sprintf("%s:%s", $controller, $action);
        if (array_key_exists($key, $this->filterCache)) {
            return $this->filterCache[$key];
        }

        $this->filterCache[$key] = [];
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getClass() === null) {
                continue;
            }

            if ($parameter->getClass()->implementsInterface(FilterInterface::class)) {
                $this->filterCache[$key][$parameter->getName()] = $parameter->getClass()->getName();
            }
        }

        return $this->filterCache[$key];
    }
}