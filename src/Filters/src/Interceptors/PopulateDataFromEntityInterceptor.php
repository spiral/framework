<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Filter;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;

final class PopulateDataFromEntityInterceptor implements CoreInterceptorInterface
{
    /**
     * @param array{filterBag: FilterBag} $parameters
     */
    public function process(string $name, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        $bag = $parameters['filterBag'];

        if ($bag->filter instanceof Filter) {
            $bag->filter->setData($bag->entity->toArray());
        }

        return $core->callAction($name, $action, $parameters);
    }
}
