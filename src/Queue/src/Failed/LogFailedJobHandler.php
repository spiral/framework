<?php

declare(strict_types=1);

namespace Spiral\Queue\Failed;

use Spiral\Exceptions\ExceptionReporterInterface;

final class LogFailedJobHandler implements FailedJobHandlerInterface
{
    public function __construct(
        private readonly ExceptionReporterInterface $reporter
    ) {
    }

    public function handle(string $driver, string $queue, string $job, mixed $payload, \Throwable $e): void
    {
        $this->reporter->report($e);
    }
}
