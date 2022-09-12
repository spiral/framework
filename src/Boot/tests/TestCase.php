<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Core\Container;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function getBootloadManager(Container $container = new Container()): BootloadManager
    {
        return new BootloadManager(
            $container,
            $container,
            $container,
            new Initializer($container, $container)
        );
    }
}
