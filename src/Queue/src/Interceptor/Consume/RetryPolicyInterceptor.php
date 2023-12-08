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
use Spiral\Queue\Options;
use Spiral\Queue\RetryPolicyInterface;

final class RetryPolicyInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        try {
            return $core->callAction($controller, $action, $parameters);
        } catch (\Throwable $e) {
            // In some cases job handler class may not exist or be just a string.
            // In this case we can't get retry policy from it.
            $class = \class_exists($controller) ? new \ReflectionClass($controller) : null;
            $policy = $this->getRetryPolicy($e, $class);

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
                    ->withHeader('attempts', (string)($attempts + 1)),
            );
        }
    }

    private function getRetryPolicy(\Throwable $exception, ?\ReflectionClass $handler): ?RetryPolicyInterface
    {
        $attribute = $handler !== null
            ? $this->reader->firstClassMetadata($handler, Attribute::class)
            : null;

        if ($exception instanceof JobException && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        $policy = $exception instanceof RetryableExceptionInterface ? $exception->getRetryPolicy() : null;

        return $policy ?? $attribute?->getRetryPolicy() ?? null;
    }
}
