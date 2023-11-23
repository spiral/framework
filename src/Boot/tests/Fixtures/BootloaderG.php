<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Attribute\BootloadConfig;

#[BootloadConfig(args: ['a' => 'b', 'c' => 'd'])]
class BootloaderG extends AbstractBootloader
{
}
