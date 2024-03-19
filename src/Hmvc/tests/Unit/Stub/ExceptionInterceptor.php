<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub;

use Spiral\Core\Context\CallContextInterface;
use Spiral\Core\HandlerInterface;
use Spiral\Core\InterceptorInterface;

final class ExceptionInterceptor implements InterceptorInterface
{
    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
    {
        throw new \RuntimeException('Intercepted');
    }
}
