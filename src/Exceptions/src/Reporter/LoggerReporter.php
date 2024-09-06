<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Reporter;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Exceptions\ExceptionReporterInterface;

class LoggerReporter implements ExceptionReporterInterface
{
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function report(\Throwable $exception): void
    {
        $this->logger->error(\sprintf(
            '%s: %s in %s at line %s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
    }
}
