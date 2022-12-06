<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\BootloadManager\CustomizableBootloadManager;
use Spiral\Bootloader\Auth\HttpAuthBootloader;

class AuthBootloader extends Bootloader
{
    public function init(CustomizableBootloadManager $bootloadManager): void
    {
        $bootloadManager->bootload([HttpAuthBootloader::class]);
    }
}
