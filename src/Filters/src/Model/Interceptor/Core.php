<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor;

use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\HandlerInterface;

/**
 * @psalm-type TParameters = array{filterBag: FilterBag}
 */
final class Core implements CoreInterface, HandlerInterface
{
    /**
     * @param-assert TParameters $parameters
     */
    public function callAction(string $controller, string $action, array $parameters = []): FilterInterface
    {
        \assert($parameters['filterBag'] instanceof FilterBag);

        return $parameters['filterBag']->filter;
    }

    public function handle(CallContext $context): FilterInterface
    {
        $args = $context->getArguments();
        \assert($args['filterBag'] instanceof FilterBag);

        return $args['filterBag']->filter;
    }
}
