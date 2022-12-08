<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Core\Container;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function getBootloadManager(Container $container = new Container()): StrategyBasedBootloadManager
    {
        $initializer = new Initializer($container, $container);

        return new StrategyBasedBootloadManager(
            new DefaultInvokerStrategy($initializer, $container, $container),
            $container,
            $initializer
        );
    }
}
