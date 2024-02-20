<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;

abstract class BaseTestCase extends TestCase
{
    protected ContainerInterface $container;

    public function setUp(): void
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
        $this->container->bindSingleton(ValidationInterface::class, ValidationProvider::class);
    }
}
