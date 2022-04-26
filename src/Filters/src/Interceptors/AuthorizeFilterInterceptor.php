<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Auth\AuthContextInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Filter;
use Spiral\Filters\ShouldBeAuthorized;

final class AuthorizeFilterInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly Container $container = new Container()
    ) {
    }

    public function process(string $name, string $action, array $parameters, CoreInterface $core): mixed
    {
        /** @var Filter $filter */
        $filter = $core->callAction($name, $action, $parameters);

        if ($filter instanceof ShouldBeAuthorized) {
            $auth = $this->container->has(AuthContextInterface::class)
                ? $this->container->get(AuthContextInterface::class)
                : null;

            if (!$filter->isAuthorized($auth)) {
                $filter->failedAuthorization();
            }
        }

        return $filter;
    }
}
