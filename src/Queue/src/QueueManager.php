<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Config\QueueConfig;

final class QueueManager implements QueueConnectionProviderInterface
{
    /** @var QueueInterface[] */
    private array $pipelines = [];

    public function __construct(
        private readonly QueueConfig $config,
        private readonly FactoryInterface $factory,
    ) {
    }

    public function getConnection(?string $name = null): QueueInterface
    {
        $name ??= $this->getDefaultDriver();
        // Replaces alias with real pipeline name
        $name = $this->config->getAliases()[$name] ?? $name;

        if (!isset($this->pipelines[$name])) {
            $this->pipelines[$name] = $this->resolveConnection($name);
        }

        return $this->pipelines[$name];
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\NotSupportedDriverException
     */
    private function resolveConnection(string $name): QueueInterface
    {
        $config = $this->config->getConnection($name);

        try {
            return $this->factory->make($config['driver'], $config);
        } catch (ContainerException $e) {
            throw new Exception\NotSupportedDriverException(
                \sprintf(
                    'Driver `%s` is not supported. Connection `%s` cannot be created. Reason: `%s`',
                    $config['driver'],
                    $name,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    private function getDefaultDriver(): string
    {
        return $this->config->getDefaultDriver();
    }
}
