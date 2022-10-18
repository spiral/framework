<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Psr\Container\ContainerInterface;

final class InvalidWithContainerInside
{
    public function __construct(ContainerInterface $container, InvalidClass $class)
    {
        $container->get('invalid');
    }
}
