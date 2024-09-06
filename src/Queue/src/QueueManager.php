<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CompatiblePipelineBuilder;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Interceptors\PipelineBuilderInterface;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Interceptor\Push\Core as PushCore;

final class QueueManager implements QueueConnectionProviderInterface
{
    /** @var QueueInterface[] */
    private array $pipelines = [];
    private readonly PipelineBuilderInterface $builder;

    public function __construct(
        private readonly QueueConfig $config,
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $factory,
        ?EventDispatcherInterface $dispatcher = null,
        ?PipelineBuilderInterface $builder = null,
    ) {
        $this->builder = $builder ?? new CompatiblePipelineBuilder($dispatcher);
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
            $driver = $this->factory->make($config['driver'], $config);

            $list = [];
            foreach ($this->config->getPushInterceptors() as $interceptor) {
                $list[] = \is_string($interceptor) || $interceptor instanceof Autowire
                    ? $this->container->get($interceptor)
                    : $interceptor;
            }

            return new Queue($this->builder->withInterceptors(...$list)->build(new PushCore($driver)));
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
