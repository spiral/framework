<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;

final class BootloaderC extends Bootloader
{
    public function init(BootloaderA $a): void {}

    public function boot(BootloaderB $b): void {}
}
