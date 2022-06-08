<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\App;

use Spiral\Console\Console;
use Spiral\Core\Container;
use Spiral\Framework\Kernel;
use Spiral\Http\Http;
use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Router\Bootloader\AnnotatedRoutesBootloader;

class App extends Kernel
{
    protected const LOAD = [
        NyholmBootloader::class,
        AnnotatedRoutesBootloader::class,
    ];

    public function getHttp(): Http
    {
        return $this->container->get(Http::class);
    }

    public function getConsole(): Console
    {
        return $this->container->get(Console::class);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
