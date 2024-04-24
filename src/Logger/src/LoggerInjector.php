<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Psr\Log\LoggerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Logger\Attribute\LoggerChannel;

/**
 * Container injector for {@see LoggerInterface}.
 * Supports {@see LoggerChannel} attribute.
 *
 * @implements InjectorInterface<LoggerInterface>
 */
final class LoggerInjector implements InjectorInterface
{
    public function __construct(
        #[Proxy]
        private readonly LogsInterface $factory
    ) {
    }

    public function createInjection(
        \ReflectionClass $class,
        \ReflectionParameter|null|string $context = null,
    ): LoggerInterface {
        $channel = \is_object($context) ? $this->extractChannelAttribute($context) : null;

        // always return default logger as injection
        return $this->factory->getLogger($channel);
    }

    /**
     * @return non-empty-string|null
     */
    private function extractChannelAttribute(\ReflectionParameter $parameter): ?string
    {
        /** @var ReflectionAttribute<LoggerChannel>[] $attributes */
        $attributes = $parameter->getAttributes(LoggerChannel::class);

        return $attributes[0]?->newInstance()->name;
    }
}
