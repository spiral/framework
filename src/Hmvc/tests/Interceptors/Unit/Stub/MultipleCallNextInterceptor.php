<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Stub;

use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

final class MultipleCallNextInterceptor implements InterceptorInterface
{
    public array $result;

    public function __construct(
        private readonly int $counter,
    ) {
    }

    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
    {
        $this->result = [];
        for ($i = 0; $i < $this->counter; ++$i) {
            $this->result[] = $handler->handle($context);
        }

        return $this->result;
    }
}
