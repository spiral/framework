<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Psr\Container\ContainerInterface;

final class ScopeController
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function construct(): string
    {
        return $this->getScopeName($this->container);
    }

    public function method(ContainerInterface $container): string
    {
        return $this->getScopeName($container);
    }

    private function getScopeName(ContainerInterface $container): string
    {
        $scope = (new \ReflectionProperty($container, 'scope'))->getValue($container);

        return (new \ReflectionProperty($scope, 'scopeName'))->getValue($scope);
    }
}
