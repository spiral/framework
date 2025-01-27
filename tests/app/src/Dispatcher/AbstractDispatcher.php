<?php

declare(strict_types=1);

namespace Spiral\App\Dispatcher;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;

abstract class AbstractDispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public static function canServe(EnvironmentInterface $env): bool
    {
        return true;
    }

    public function serve(): mixed
    {
        $scope = (new \ReflectionProperty($this->container, 'scope'))->getValue($this->container);

        return [
            'dispatcher' => $this->container->get(static::class),
            'foo' => $this->container->has('foo') ? $this->container->get('foo') : null,
            'scope' => (new \ReflectionProperty($scope, 'scopeName'))->getValue($scope),
        ];
    }
}
