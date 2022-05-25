<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Factory;

abstract class BaseTest extends \Spiral\Tests\Core\Internal\BaseTest
{
    protected function make(string $class, array $args = [], string $context = null): mixed
    {
        return $this->createFactory()->make($class, $args, $context);
    }
}
