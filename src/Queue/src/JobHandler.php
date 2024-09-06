<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\Attribute\Proxy;
use Spiral\Core\InvokerInterface;
use Spiral\Queue\Exception\JobException;

/**
 * Handler which can invoke itself.
 */
abstract class JobHandler implements HandlerInterface
{
    /**
     * Default function with method injection.
     */
    protected const HANDLE_FUNCTION = 'invoke';

    public function __construct(
        #[Proxy] protected InvokerInterface $invoker,
    ) {
    }

    public function handle(string $name, string $id, mixed $payload, array $headers = []): void
    {
        try {
            $params = ['payload' => $payload, 'id' => $id, 'headers' => $headers];

            if (\is_array($payload)) {
                $params = \array_merge($params, $payload);
            }

            $this->invoker->invoke([$this, $this->getHandlerMethod()], $params);
        } catch (\Throwable $e) {
            $message = \sprintf('[%s] %s', static::class, $e->getMessage());
            throw new JobException($message, (int)$e->getCode(), $e);
        }
    }

    protected function getHandlerMethod(): string
    {
        return static::HANDLE_FUNCTION;
    }
}
