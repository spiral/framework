<?php

declare(strict_types=1);

namespace Spiral\Router\Target;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Core\CoreInterface;
use Spiral\Core\Internal\Proxy;
use Spiral\Core\ScopeInterface;
use Spiral\Interceptors\Handler\AutowireHandler;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Router\CoreHandler;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\TargetInterface;
use Spiral\Router\UriHandler;
use Spiral\Telemetry\TracerInterface;

/**
 * @psalm-import-type Matches from UriHandler
 */
abstract class AbstractTarget implements TargetInterface
{
    // Automatically prepend HTTP verb to all action names.
    public const RESTFUL = 1;

    private HandlerInterface|CoreInterface|null $pipeline = null;
    private ?CoreHandler $handler = null;
    private bool $verbActions;

    public function __construct(
        private array $defaults,
        private array $constrains,
        int $options = 0,
        private string $defaultAction = 'index'
    ) {
        $this->verbActions = ($options & self::RESTFUL) === self::RESTFUL;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function getConstrains(): array
    {
        return $this->constrains;
    }

    /**
     * @mutation-free
     * @deprecated Use {@see withHandler()} instead.
     */
    public function withCore(HandlerInterface|CoreInterface $core): TargetInterface
    {
        $target = clone $this;
        $target->pipeline = $core;
        $target->handler = null;

        return $target;
    }

    /**
     * @mutation-free
     */
    public function withHandler(HandlerInterface $handler): TargetInterface
    {
        $target = clone $this;
        $target->pipeline = $handler;
        $target->handler = null;

        return $target;
    }

    public function getHandler(ContainerInterface $container, array $matches): Handler
    {
        return $this->coreHandler($container)->withContext(
            $this->resolveController($matches),
            $this->resolveAction($matches) ?? $this->defaultAction,
            $matches
        )->withVerbActions($this->verbActions);
    }

    protected function coreHandler(ContainerInterface $container): CoreHandler
    {
        if ($this->handler !== null) {
            return $this->handler;
        }

        $scope = Proxy::create(new \ReflectionClass(ScopeInterface::class), null, new \Spiral\Core\Attribute\Proxy());

        try {
            // construct on demand
            $this->handler = new CoreHandler(
                match (false) {
                    $this->pipeline === null => $this->pipeline,
                    $container->has(HandlerInterface::class) => new AutowireHandler($container),
                    default => $container->get(HandlerInterface::class),
                },
                $scope,
                $container->get(ResponseFactoryInterface::class),
                $container->get(TracerInterface::class)
            );

            return $this->handler;
        } catch (ContainerExceptionInterface $e) {
            throw new TargetException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Return controller class name.
     *
     * @param Matches $matches
     *
     * @throws TargetException
     */
    abstract protected function resolveController(array $matches): string;

    /**
     * Return target controller action.
     *
     * @param Matches $matches
     *
     * @throws TargetException
     */
    abstract protected function resolveAction(array $matches): ?string;
}
