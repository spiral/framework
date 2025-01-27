<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Attribute\BootloadConfig;

#[BootloadConfig(allowEnv: ['APP_DEBUG' => true], denyEnv: ['APP_DEBUG' => true])]
class BootloaderJ extends AbstractBootloader {}
