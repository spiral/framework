<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\CoreInterceptorInterface as LegacyInterceptor;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Queue\Exception\StateException;
use Spiral\Queue\Failed\FailedJobHandlerInterface;

final class ErrorHandlerInterceptor implements LegacyInterceptor, InterceptorInterface
{
    public function __construct(
        private readonly FailedJobHandlerInterface $handler
    ) {
    }

    /** @psalm-suppress ParamNameMismatch */
    public function process(string $name, string $action, array $parameters, CoreInterface $core): mixed
    {
        try {
            return $core->callAction($name, $action, $parameters);
        } catch (\Throwable $e) {
            if (!$e instanceof StateException) {
                $this->handler->handle(
                    $parameters['driver'],
                    $parameters['queue'],
                    $name,
                    $parameters['payload'],
                    $e
                );
            }

            throw $e;
        }
    }

    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
    {
        try {
            return $handler->handle($context);
        } catch (\Throwable $e) {
            $args = $context->getArguments();
            if (!$e instanceof StateException) {
                $this->handler->handle(
                    $args['driver'],
                    $args['queue'],
                    $context->getTarget()->getPath()[0],
                    $args['payload'],
                    $e,
                );
            }

            throw $e;
        }
    }
}
