<?php

declare(strict_types=1);

namespace Spiral\Monolog\Config;

use Monolog\Logger;
use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Monolog\Exception\ConfigException;

final class MonologConfig extends InjectableConfig
{
    public const CONFIG = 'monolog';

    /** @var array */
    protected $config = [
        'globalLevel' => Logger::DEBUG,
        'handlers'    => [],
    ];

    public function getEventLevel(): int
    {
        return $this->config['globalLevel'] ?? Logger::DEBUG;
    }

    /**
     * @return \Generator|Autowire[]
     * @throws ConfigException
     */
    public function getHandlers(string $channel): \Generator
    {
        if (empty($this->config['handlers'][$channel])) {
            return;
        }

        foreach ($this->config['handlers'][$channel] as $handler) {
            if (\is_object($handler) && !$handler instanceof Autowire) {
                yield $handler;
                continue;
            }

            $wire = $this->wire($handler);
            if (\is_null($wire)) {
                throw new ConfigException(\sprintf('Invalid handler definition for channel `%s`.', $channel));
            }

            yield $wire;
        }
    }

    public function getProcessors(string $channel): \Generator
    {
        if (empty($this->config['processors'][$channel])) {
            return;
        }

        foreach ($this->config['processors'][$channel] as $processor) {
            if (\is_object($processor) && !$processor instanceof Autowire) {
                yield $processor;
                continue;
            }

            $wire = $this->wire($processor);
            if (\is_null($wire)) {
                throw new ConfigException(\sprintf('Invalid processor definition for channel `%s`.', $channel));
            }

            yield $wire;
        }
    }

    private function wire(Autowire|string|array $definition): ?Autowire
    {
        if ($definition instanceof Autowire) {
            return $definition;
        }

        if (\is_string($definition)) {
            return new Autowire($definition);
        }

        if (isset($definition['class'])) {
            return new Autowire($definition['class'], $definition['options'] ?? []);
        }

        return null;
    }
}
