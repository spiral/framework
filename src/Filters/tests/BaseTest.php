<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;

abstract class BaseTest extends TestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->bindSingleton(ValidationInterface::class, ValidationProvider::class);
    }
}
