<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Auth\AuthContextInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Exception\AuthorizationException;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\ShouldBeAuthorized;

final class AuthorizeFilterInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly Container $container = new Container()
    ) {
    }

    public function process(string $name, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        /** @var FilterBag $bag */
        $bag = $parameters['filterBag'];

        if ($bag->filter instanceof ShouldBeAuthorized) {
            $auth = $this->container->has(AuthContextInterface::class)
                ? $this->container->get(AuthContextInterface::class)
                : null;

            if (!$bag->filter->isAuthorized($auth)) {
                throw new AuthorizationException();
            }
        }

        return $core->callAction($name, $action, $parameters);
    }
}
