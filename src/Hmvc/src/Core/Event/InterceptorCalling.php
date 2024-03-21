<?php

declare(strict_types=1);

namespace Spiral\Core\Event;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Interceptors\InterceptorInterface;

/**
 * @deprecated use {@see \Spiral\Interceptors\Event\InterceptorCalling} instead
 */
final class InterceptorCalling
{
    public function __construct(
        public readonly string $controller,
        public readonly string $action,
        public readonly array $parameters,
        public readonly CoreInterceptorInterface|InterceptorInterface $interceptor,
    ) {
    }
}
