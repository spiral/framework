<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface as LegacyInterceptor;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\InterceptorPipeline;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Interceptor\Push\Core as PushCore;

final class QueueManager implements QueueConnectionProviderInterface
{
    /** @var QueueInterface[] */
    private array $pipelines = [];

    public function __construct(
        private readonly QueueConfig $config,
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $factory,
        private readonly ?EventDispatcherInterface $dispatcher = null
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
            $driver = $this->factory->make($config['driver'], $config);
            $pipeline = (new InterceptorPipeline($this->dispatcher))->withHandler(new PushCore($driver));

            foreach ($this->config->getPushInterceptors() as $interceptor) {
                if (\is_string($interceptor) || $interceptor instanceof Autowire) {
                    $interceptor = $this->container->get($interceptor);
                }

                \assert($interceptor instanceof LegacyInterceptor || $interceptor instanceof InterceptorInterface);
                $pipeline->addInterceptor($interceptor);
            }

            return new Queue($pipeline);
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
