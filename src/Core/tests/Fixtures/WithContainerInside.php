<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Psr\Container\ContainerInterface;

final class WithContainerInside
{
    public function __construct(ContainerInterface $container)
    {
        $container->get('invalid');
    }
}
