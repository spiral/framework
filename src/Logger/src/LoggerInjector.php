<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Psr\Log\LoggerInterface;
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
    public const DEFAULT_CHANNEL = 'default';

    public function __construct(
        private readonly LogsInterface $factory,
    ) {
    }

    /**
     * @param \ReflectionParameter|string|null $context may use extended context if possible.
     */
    public function createInjection(
        \ReflectionClass $class,
        \ReflectionParameter|null|string $context = null,
    ): LoggerInterface {
        $channel = \is_object($context) ? $this->extractChannelAttribute($context) : null;

        // Check that null argument is available
        $channel ??= (new \ReflectionMethod($this->factory, 'getLogger'))->getParameters()[0]->allowsNull()
            ? null
            : self::DEFAULT_CHANNEL;

        return $this->factory->getLogger($channel);
    }

    /**
     * @return non-empty-string|null
     */
    private function extractChannelAttribute(\ReflectionParameter $parameter): ?string
    {
        /** @var \ReflectionAttribute<LoggerChannel>[] $attributes */
        $attributes = $parameter->getAttributes(LoggerChannel::class);

        return $attributes[0]?->newInstance()->name;
    }
}
