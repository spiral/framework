<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Stub;

use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

final class ExceptionInterceptor implements InterceptorInterface
{
    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
    {
        throw new \RuntimeException('Intercepted');
    }
}
