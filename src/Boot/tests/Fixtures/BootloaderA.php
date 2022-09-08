<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;

class BootloaderA extends Bootloader
{
    public const BINDINGS = [
        'a' => 'a'
    ];
}
