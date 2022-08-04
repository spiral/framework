<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor;

use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;

final class Core implements CoreInterface
{
    /**
     * @param array{filterBag: FilterBag}|array<string, mixed> $parameters
     */
    public function callAction(string $controller, string $action, array $parameters = []): FilterInterface
    {
        return $parameters['filterBag']->filter;
    }
}
