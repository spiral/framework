<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\Exception\ConfigException;

final class LogFactory implements LogsInterface, InjectorInterface, ResettableInterface
{
    /**
     * Default logger channel (supplied via injection)
     *
     * @deprecated wil be removed in 3.0. Use {@see \Spiral\Monolog\Config\MonologConfig::DEFAULT_CHANNEL}
     */
    public const DEFAULT = 'default';

    /** @var MonologConfig */
    private $config;

    /** @var LoggerInterface */
    private $default;

    /** @var FactoryInterface */
    private $factory;

    /** @var HandlerInterface|null */
    private $eventHandler;

    /**
     * @param MonologConfig $config
     * @param ListenerRegistryInterface $listenerRegistry
     * @param FactoryInterface $factory
     */
    public function __construct(
        MonologConfig $config,
        ListenerRegistryInterface $listenerRegistry,
        FactoryInterface $factory
    ) {
        $this->config = $config;
        $this->factory = $factory;
        $this->eventHandler = new EventHandler($listenerRegistry, $config->getEventLevel());
    }

    /**
     * @inheritdoc
     */
    public function getLogger(string $channel = null): LoggerInterface
    {
        if ($channel === null || $channel == self::DEFAULT) {
            if ($this->default !== null) {
                // we should use only one default logger per system
                return $this->default;
            }

            return $this->default = new Logger(
                self::DEFAULT,
                $this->getHandlers(self::DEFAULT),
                $this->getProcessors(self::DEFAULT)
            );
        }

        return new Logger(
            $channel,
            $this->getHandlers($channel),
            $this->getProcessors($channel)
        );
    }

    /**
     * @inheritdoc
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
    {
        // always return default logger as injection
        return $this->getLogger();
    }

    public function reset()
    {
        if ($this->default instanceof ResettableInterface) {
            $this->default->reset();
        }
    }

    /**
     * Get list of channel specific handlers.
     *
     * @param string $channel
     * @return array
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
     * @param string $channel
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
