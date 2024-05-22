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

        if ($channel === null) {
            /**
             * Array of flags to check if the logger allows null argument
             *
             * @var array<class-string<LogsInterface>, bool> $cache
             */
            static $cache = [];

            $cache[$this->factory::class] = (new \ReflectionMethod($this->factory, 'getLogger'))
                ->getParameters()[0]->allowsNull();

            $channel = $cache[$this->factory::class] ? null : self::DEFAULT_CHANNEL;
        }

        return $this->factory->getLogger($channel);
    }

    /**
     * @return non-empty-string|null
     */
    private function extractChannelAttribute(\ReflectionParameter $parameter): ?string
    {
        return ($parameter->getAttributes(LoggerChannel::class)[0] ?? null)?->newInstance()->name;
    }
}
