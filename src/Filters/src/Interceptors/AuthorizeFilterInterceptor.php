<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Psr\Container\ContainerInterface;
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
    /** @param Container $container */
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * @param array{filterBag: FilterBag} $parameters
     */
    public function process(string $name, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        $filter = $core->callAction($name, $action, $parameters);

        if ($filter instanceof ShouldBeAuthorized) {
            $auth = $this->container->has(AuthContextInterface::class)
                ? $this->container->get(AuthContextInterface::class)
                : null;

            if (!$filter->isAuthorized($auth)) {
                throw new AuthorizationException();
            }
        }

        return $filter;
    }
}
