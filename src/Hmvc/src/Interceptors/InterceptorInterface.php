<?php

declare(strict_types=1);

namespace Spiral\Interceptors;

use Spiral\Interceptors\Context\CallContextInterface;

interface InterceptorInterface
{
    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed;
}
