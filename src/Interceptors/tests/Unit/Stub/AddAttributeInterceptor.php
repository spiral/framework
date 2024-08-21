<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Stub;

use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

final class AddAttributeInterceptor implements InterceptorInterface
{
    public function __construct(
        private string $attribute,
        private mixed $value,
    ) {
    }

    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
    {
        return $handler->handle($context->withAttribute($this->attribute, $this->value));
    }
}
