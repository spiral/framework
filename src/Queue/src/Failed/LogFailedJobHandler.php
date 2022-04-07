<?php

declare(strict_types=1);

namespace Spiral\Queue\Failed;

use Spiral\Exceptions\ErrorHandlerInterface;

final class LogFailedJobHandler implements FailedJobHandlerInterface
{
    public function __construct(
        private readonly ErrorHandlerInterface $errorhandler
    ) {
    }

    public function handle(string $driver, string $queue, string $job, array $payload, \Throwable $e): void
    {
        $this->errorhandler->shouldReport($e) && $this->errorhandler->report($e);
    }
}
