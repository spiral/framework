<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;

/**
 * @psalm-type TParameters = array{filterBag: FilterBag}
 */
final class PopulateDataFromEntityInterceptor implements CoreInterceptorInterface
{
    /**
     * @param-assert TParameters $parameters
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        \assert($parameters['filterBag'] instanceof FilterBag);

        $bag = $parameters['filterBag'];

        if ($bag->filter instanceof Filter) {
            $bag->filter->setData($bag->entity->toArray());
        }

        return $core->callAction($controller, $action, $parameters);
    }
}
