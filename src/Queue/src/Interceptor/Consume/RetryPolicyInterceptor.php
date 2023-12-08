<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Attribute\RetryPolicy as Attribute;
use Spiral\Queue\Exception\JobException;
use Spiral\Queue\Exception\RetryableExceptionInterface;
use Spiral\Queue\Exception\RetryException;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Options;
use Spiral\Queue\RetryPolicyInterface;

final class RetryPolicyInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly HandlerRegistryInterface $registry,
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        try {
            return $core->callAction($controller, $action, $parameters);
        } catch (\Throwable $e) {
            if (!\class_exists($controller)) {
                $controller = $this->registry->getHandler($controller)::class;
            }

            $policy = $this->getRetryPolicy($e, new \ReflectionClass($controller));

            if ($policy === null) {
                throw $e;
            }

            $headers = $parameters['headers'] ?? [];
            $attempts = (int)($headers['attempts'][0] ?? 0);

            if ($policy->isRetryable($e, $attempts) === false) {
                throw $e;
            }

            throw new RetryException(
                reason: $e->getMessage(),
                options: (new Options())
                    ->withDelay($policy->getDelay($attempts))
                    ->withHeader('attempts', (string)($attempts + 1))
            );
        }
    }

    private function getRetryPolicy(\Throwable $exception, \ReflectionClass $handler): ?RetryPolicyInterface
    {
        $attribute = $this->reader->firstClassMetadata($handler, Attribute::class);

        if ($exception instanceof JobException && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        $policy = $exception instanceof RetryableExceptionInterface ? $exception->getRetryPolicy() : null;

        return $policy ?? $attribute?->getRetryPolicy() ?? null;
    }
}
