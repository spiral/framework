<?php

declare(strict_types=1);

namespace Spiral\Router;

interface GroupRegistryInterface extends \IteratorAggregate
{
    /**
     * @param string $name
     * @return RouteGroupInterface
     */
    public function getGroup(string $name): RouteGroupInterface;
}
