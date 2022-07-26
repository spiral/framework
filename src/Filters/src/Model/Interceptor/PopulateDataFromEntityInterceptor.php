<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;

final class PopulateDataFromEntityInterceptor implements CoreInterceptorInterface
{
    /**
     * @param array{filterBag: FilterBag} $parameters
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        $bag = $parameters['filterBag'];

        if ($bag->filter instanceof Filter) {
            $bag->filter->setData($bag->entity->toArray());
        }

        return $core->callAction($controller, $action, $parameters);
    }
}
