<?php

declare(strict_types=1);

namespace Spiral\Queue\Failed;

use Spiral\Exceptions\ErrorReporterInterface;

final class LogFailedJobHandler implements FailedJobHandlerInterface
{
    public function __construct(
        private readonly ErrorReporterInterface $reporter
    ) {
    }

    public function handle(string $driver, string $queue, string $job, array $payload, \Throwable $e): void
    {
        $this->reporter->report($e);
    }
}
