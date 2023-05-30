<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Tag\Stub;

use Spiral\Core\Attribute\Tag;
use Spiral\Core\Container\InjectorInterface;

/**
 * @implements InjectorInterface<LoggerInterface>
 */
#[Tag('logger')]
final class LoggerInjector implements InjectorInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function createInjection(\ReflectionClass $class, ?string $context = null): LoggerInterface
    {
        return $this->logger;
    }
}
