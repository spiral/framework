<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use Spiral\Core\Container;
use Spiral\Core\Options;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
    }
}
