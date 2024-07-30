<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Handler;

use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;

/**
 * Handler that just invokes PHP callable without any additional logic.
 */
final class CallableHandler implements HandlerInterface
{
    public function handle(CallContextInterface $context): mixed
    {
        $callable = $context->getTarget()->getCallable();
        \is_callable($callable) or throw new \RuntimeException('Callable not found in the call context.');

        return $callable(...$context->getArguments());
    }
}
