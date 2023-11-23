<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Attribute\BootloaderRules;

#[BootloaderRules(enabled: false)]
class BootloaderF extends AbstractBootloader
{
}
