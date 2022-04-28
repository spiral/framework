<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Core\CoreInterface;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;

final class Core implements CoreInterface
{
    public function callAction(string $name, string $action, array $parameters = []): FilterInterface
    {
        /** @var FilterBag $bag */
        $bag = $parameters['filterBag'];

        return $bag->filter;
    }
}
