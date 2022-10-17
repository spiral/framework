<?php

declare(strict_types=1);

namespace Spiral\Queue;

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
        protected InvokerInterface $invoker
    ) {
    }

    public function handle(string $name, string $id, array $payload, array $headers = []): void
    {
        try {
            $this->invoker->invoke(
                [$this, $this->getHandlerMethod()],
                \array_merge(['payload' => $payload, 'id' => $id, 'headers' => $headers], $payload)
            );
        } catch (\Throwable $e) {
            $message = \sprintf('[%s] %s', $this::class, $e->getMessage());
            throw new JobException($message, (int)$e->getCode(), $e);
        }
    }

    protected function getHandlerMethod(): string
    {
        return static::HANDLE_FUNCTION;
    }
}
