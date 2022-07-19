<?php

declare(strict_types=1);

namespace Spiral\Filters\Dto\Interceptors;

use Psr\Container\ContainerInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Dto\FilterBag;
use Spiral\Filters\Dto\FilterInterface;
use Spiral\Filters\Exception\AuthorizationException;
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
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        $filter = $core->callAction($controller, $action, $parameters);

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
