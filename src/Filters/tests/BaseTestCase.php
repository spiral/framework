<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;

abstract class BaseTestCase extends TestCase
{
    protected ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->bindSingleton(ValidationInterface::class, ValidationProvider::class);
    }
}
