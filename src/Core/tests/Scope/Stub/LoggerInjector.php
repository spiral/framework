<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

use Spiral\Core\Container\InjectorInterface;

/**
 * @implements InjectorInterface<LoggerInterface>
 */
final class LoggerInjector implements InjectorInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function createInjection(\ReflectionClass $class, ?string $context = null): LoggerInterface
    {
        return $this->logger;
    }
}
