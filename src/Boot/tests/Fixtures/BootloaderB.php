<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;

class BootloaderB extends Bootloader implements DependedInterface
{
    public const BINDINGS = [
        'b' => 'b'
    ];

    public function defineDependencies(): array
    {
        return [BootloaderA::class];
    }
}
