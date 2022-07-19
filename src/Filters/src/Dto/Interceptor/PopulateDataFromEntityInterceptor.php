<?php

declare(strict_types=1);

namespace Spiral\Filters\Dto\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Dto\Filter;
use Spiral\Filters\Dto\FilterBag;
use Spiral\Filters\Dto\FilterInterface;

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
