<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use Spiral\Boot\BootloadManager\CustomizableBootloadManager;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Core\Container;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function getBootloadManager(Container $container = new Container()): CustomizableBootloadManager
    {
        $initializer = new Initializer($container, $container);

        return new CustomizableBootloadManager(
            new DefaultInvokerStrategy($initializer, $container, $container),
            $container,
            $initializer
        );
    }
}
