<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit\Stub;

use Spiral\Core\Context\CallContextInterface;
use Spiral\Core\HandlerInterface;
use Spiral\Core\InterceptorInterface;

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
