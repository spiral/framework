<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub;

use Spiral\Core\Context\CallContextInterface;
use Spiral\Core\HandlerInterface;
use Spiral\Core\InterceptorInterface;

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
