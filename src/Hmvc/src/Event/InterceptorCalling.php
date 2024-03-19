<?php

declare(strict_types=1);

namespace Spiral\Core\Event;

use Spiral\Core\Context\CallContextInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\InterceptorInterface;

final class InterceptorCalling
{
    public function __construct(
        /** @deprecated will be removed in Spiral v4.0. Use $context instead */
        public readonly string $controller,
        /** @deprecated will be removed in Spiral v4.0. Use $context instead */
        public readonly string $action,
        /** @deprecated will be removed in Spiral v4.0. Use $context instead */
        public readonly array $parameters,
        public readonly CoreInterceptorInterface|InterceptorInterface $interceptor,
        public readonly CallContextInterface $context,
    ) {
    }
}
