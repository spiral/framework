<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;

class BootloaderB extends Bootloader implements DependedInterface
{
    public const BINDINGS = [
        'b' => true
    ];

    public function defineDependencies(): array
    {
        return [BootloaderA::class];
    }
}
