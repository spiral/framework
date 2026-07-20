<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Attribute\BootloadConfig;

#[BootloadConfig(enabled: false)]
final class BootloaderF extends AbstractBootloader {}
