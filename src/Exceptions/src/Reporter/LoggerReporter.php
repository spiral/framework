<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Reporter;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Exceptions\ExceptionReporterInterface;

class LoggerReporter implements ExceptionReporterInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function report(\Throwable $exception): void
    {
        if (!$this->container->has(LoggerInterface::class)) {
            return;
        }

        /** @var LoggerInterface $logger */
        $logger = $this->container->get(LoggerInterface::class);

        $logger->error(\sprintf(
            '%s: %s in %s at line %s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
    }
}
