<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\App;

use Spiral\Console\Console;
use Spiral\Framework\Kernel;
use Spiral\Http\Bootloader\DiactorosBootloader;
use Spiral\Http\Http;
use Spiral\Router\Bootloader\AnnotatedRoutesBootloader;

class App extends Kernel
{
    protected const LOAD = [
        DiactorosBootloader::class,
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
}
