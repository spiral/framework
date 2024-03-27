<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Event;

use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\InterceptorInterface;

final class InterceptorCalling
{
    public function __construct(
        public readonly CallContextInterface $context,
        public readonly InterceptorInterface $interceptor,
    ) {
    }
}
