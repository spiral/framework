<?php

declare(strict_types=1);

namespace Spiral\Core\Event;

use Spiral\Core\Context\CallContextInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\Reborn\InterceptorInterface;

final class InterceptorCalling
{
    public function __construct(
        public readonly string $controller,
        public readonly string $action,
        public readonly array $parameters,
        public readonly CoreInterceptorInterface|InterceptorInterface $interceptor,
        public readonly CallContextInterface $context,
    ) {
    }
}
