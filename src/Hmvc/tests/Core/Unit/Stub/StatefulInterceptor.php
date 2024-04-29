<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub;

use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

final class StatefulInterceptor implements InterceptorInterface
{
    public CallContextInterface $context;
    public HandlerInterface $next;
    public mixed $result;

    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
    {
        $this->context = $context;
        $this->next = $handler;
        return $this->result = $handler->handle($context);
    }
}
