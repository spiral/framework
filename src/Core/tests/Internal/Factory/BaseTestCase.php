<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Factory;

abstract class BaseTestCase extends \Spiral\Tests\Core\Internal\BaseTestCase
{
    protected function make(string $class, array $args = [], ?string $context = null): mixed
    {
        return $this->createFactory()->make($class, $args, $context);
    }
}
