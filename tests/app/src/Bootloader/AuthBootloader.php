<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Bootloader\Auth\HttpAuthBootloader;

class AuthBootloader extends Bootloader
{
    public function init(StrategyBasedBootloadManager $bootloadManager): void
    {
        $bootloadManager->bootload([HttpAuthBootloader::class]);
    }
}
