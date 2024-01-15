<?php

declare(strict_types=1);

namespace Spiral\App\Dispatcher;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Core\Attribute\Proxy;

abstract class AbstractDispatcher implements DispatcherInterface
{
    public function __construct(
        #[Proxy] private readonly ContainerInterface $container,
    ) {
    }

    public function canServe(): bool
    {
        return true;
    }

    public function serve(): mixed
    {
        return $this->container->get('foo');
    }
}
