<?php

declare(strict_types=1);

namespace Spiral\Monolog;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\ResettableInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Logger\ListenerRegistryInterface;
use Spiral\Logger\LoggerInjector;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\Exception\ConfigException;

/**
 * @implements InjectorInterface<Logger>
 */
final class LogFactory implements LogsInterface, InjectorInterface, ResettableInterface
{
    private ?LoggerInterface $default = null;
    private readonly HandlerInterface $eventHandler;

    public function __construct(
        private readonly MonologConfig $config,
        ListenerRegistryInterface $listenerRegistry,
        private readonly FactoryInterface $factory
    ) {
        $this->eventHandler = new EventHandler($listenerRegistry, $config->getEventLevel());
    }

    public function getLogger(?string $channel = null): LoggerInterface
    {
        $default = $this->config->getDefault();

        if ($channel === null || $channel === $default) {
            if ($this->default !== null) {
                // we should use only one default logger per system
                return $this->default;
            }

            return $this->default = new Logger(
                $default,
                $this->getHandlers($default),
                $this->getProcessors($default)
            );
        }

        return new Logger(
            $channel,
            $this->getHandlers($channel),
            $this->getProcessors($channel)
        );
    }

    /**
     * @deprecated use {@see LoggerInjector} as an injector instead.
     */
    public function createInjection(\ReflectionClass $class, ?string $context = null): LoggerInterface
    {
        return $this->getLogger();
    }

    public function reset(): void
    {
        if ($this->default instanceof ResettableInterface) {
            $this->default->reset();
        }
    }

    /**
     * Get list of channel specific handlers.
     *
     *
     * @throws ConfigException
     */
    protected function getHandlers(string $channel): array
    {
        // always include default handler
        $handlers = [];

        foreach ($this->config->getHandlers($channel) as $handler) {
            if (!$handler instanceof Autowire) {
                $handlers[] = $handler;
                continue;
            }

            try {
                $handlers[] = $handler->resolve($this->factory);
            } catch (ContainerExceptionInterface $e) {
                throw new ConfigException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $handlers[] = $this->eventHandler;

        return $handlers;
    }

    /**
     * Get list of channel specific log processors.
     *
     * @return callable[]
     */
    protected function getProcessors(string $channel): array
    {
        $processors = [];
        foreach ($this->config->getProcessors($channel) as $processor) {
            if (!$processor instanceof Autowire) {
                $processors[] = $processor;
                continue;
            }

            try {
                $processors[] = $processor->resolve($this->factory);
            } catch (ContainerExceptionInterface $e) {
                throw new ConfigException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if ($processors === []) {
            $processors[] = new PsrLogMessageProcessor();
        }

        return $processors;
    }
}
