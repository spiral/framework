<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Spiral\Core\Container;
use Spiral\Core\Scope;

abstract class ScopedTestCase extends BaseTestCase
{
    protected function runTest(): mixed
    {
        return $this->container->runScope(new Scope('http'), function (Container $container): mixed {
            $this->container = $container;
            return parent::runTest();
        });
    }
}
