<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Attribute\BootloadConfig;

#[BootloadConfig(denyEnv: [
    'RR_MODE' => 'http',
    'APP_ENV' => ['production', 'prod'],
    'DB_HOST' => 'db.example.com',
])]
class BootloaderI extends AbstractBootloader {}
