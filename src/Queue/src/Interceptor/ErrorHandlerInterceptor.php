<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Failed\FailedJobHandlerInterface;

final class ErrorHandlerInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly FailedJobHandlerInterface $handler
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        try {
            return $core->callAction($controller, $action, $parameters);
        } catch (\Throwable $e) {
            $this->handler->handle(
                $parameters['driver'],
                $parameters['queue'],
                $controller,
                $parameters['payload'],
                $e
            );
        }

        return null;
    }
}
